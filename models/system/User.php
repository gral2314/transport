<?php

namespace app\models\system;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model.
 *
 * @property int $id
 * @property string $usercode
 * @property string $username
 * @property string|null $last_name
 * @property int $idgroup
 * @property int|null $codeemploye
 * @property string $password
 * @property string $authkey
 * @property string $imagen
 * @property string $accesstoken
 * @property string|null $fcm_token
 * @property string|null $fcm_expire
 * @property string|null $token_reset_pass
 * @property string|null $token_rest_time
 * @property string $active
 * @property string $locked
 * @property string $online
 * @property string|null $last_ip
 * @property string|null $lastconection
 * @property int $failed_login_attempts
 * @property string|null $blocked_until
 * @property string|null $createdate
 * @property string|null $createtime
 * @property int|null $createuser
 * @property string|null $updatedate
 * @property string|null $updatetime
 * @property int|null $updateuser
 */
class User extends ActiveRecord implements IdentityInterface
{
    private const MAX_FAILED_ATTEMPTS = 3;
    private const BLOCK_DURATION_MINUTES = 30;

    /** @var string Mensaje del ultimo intento de autenticacion. */
    private static string $lastAuthError = '';

    public static function tableName()
    {
        return '{{%users}}';
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'active' => 'Y']);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['accesstoken' => $token, 'active' => 'Y']);
    }

    public static function findByUserCode($usercode)
    {
        return static::findOne(['usercode' => $usercode]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public static function findByPasswordResetToken($token)
    {
        if (empty($token)) {
            return null;
        }

        return static::findOne([
            'token_reset_pass' => $token,
            'active' => 'Y',
        ]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->authkey;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Valida password y aplica reglas de bloqueo por intentos.
     */
    public function validatePassword($password)
    {
        self::$lastAuthError = '';

        if ($this->active !== 'Y') {
            self::$lastAuthError = 'El usuario esta inactivo.';
            return false;
        }

        if ($this->isBlocked()) {
            if (!empty($this->blocked_until)) {
                self::$lastAuthError = 'El usuario ha sido bloqueado hasta ' . $this->blocked_until . '.';
            } else {
                self::$lastAuthError = 'El usuario ha sido bloqueado.';
            }
            return false;
        }

        if ($this->password !== md5($password)) {
            $this->registerFailedAttempt();
            return false;
        }

        $this->resetFailedAttempts();
        return true;
    }

    public static function getLastAuthError(): string
    {
        return self::$lastAuthError;
    }

    public function setPassword($password)
    {
        $this->password = md5($password);
    }

    public function isBlocked(): bool
    {
        if ($this->locked !== 'Y') {
            return false;
        }

        if (empty($this->blocked_until)) {
            return true;
        }

        $blockedUntilTs = strtotime((string) $this->blocked_until);
        if ($blockedUntilTs === false || $blockedUntilTs > time()) {
            return true;
        }

        // Desbloqueo automatico al expirar la ventana de bloqueo.
        $this->locked = 'N';
        $this->blocked_until = null;
        $this->failed_login_attempts = 0;
        $this->touchAuditFields();
        $this->save(false, ['locked', 'blocked_until', 'failed_login_attempts', 'updatedate', 'updatetime', 'updateuser']);

        return false;
    }

    public function UpdateAfterLogin($usercode)
    {
        try {
            $user = self::findByUserCode($usercode);
            if ($user === null) {
                return;
            }

            $dateTime = date('Y-m-d H:i:s');
            $user->online = 'Y';
            $user->lastconection = $dateTime;
            $user->last_ip = Yii::$app->request->userIP ?? null;
            $user->touchAuditFields();
            $user->save(false, ['online', 'lastconection', 'last_ip', 'updatedate', 'updatetime', 'updateuser']);
        } catch (\Throwable $th) {
            Yii::warning('No se pudo actualizar estado login: ' . $th->getMessage(), __METHOD__);
        }
    }

    public function UpdateBeforeLogout($usercode)
    {
        try {
            $user = self::findByUserCode($usercode);
            if ($user === null) {
                return;
            }

            $dateTime = date('Y-m-d H:i:s');
            $user->online = 'N';
            $user->lastconection = $dateTime;
            $user->touchAuditFields();
            $user->save(false, ['online', 'lastconection', 'updatedate', 'updatetime', 'updateuser']);
        } catch (\Throwable $th) {
            Yii::warning('No se pudo actualizar estado logout: ' . $th->getMessage(), __METHOD__);
        }
    }

    private function registerFailedAttempt(): void
    {
        // Compatibilidad con default legacy failed_login_attempts=3.
        $attempts = (int) $this->failed_login_attempts;
        if ($attempts >= self::MAX_FAILED_ATTEMPTS) {
            $attempts = 0;
        }

        $attempts++;
        $this->failed_login_attempts = $attempts;

        if ($attempts >= self::MAX_FAILED_ATTEMPTS) {
            $this->locked = 'Y';
            $this->online = 'N';
            $this->blocked_until = date('Y-m-d H:i:s', time() + (self::BLOCK_DURATION_MINUTES * 60));
            self::$lastAuthError = 'El usuario ha sido bloqueado por multiples intentos fallidos.';
        } else {
            $remaining = self::MAX_FAILED_ATTEMPTS - $attempts;
            self::$lastAuthError = 'Credenciales incorrectas. Intentos restantes: ' . $remaining . '.';
        }

        $this->touchAuditFields();
        $this->save(false, ['failed_login_attempts', 'locked', 'online', 'blocked_until', 'updatedate', 'updatetime', 'updateuser']);
    }

    private function resetFailedAttempts(): void
    {
        if ((int) $this->failed_login_attempts === 0 && $this->locked === 'N' && $this->blocked_until === null) {
            return;
        }

        $this->failed_login_attempts = 0;
        $this->locked = 'N';
        $this->blocked_until = null;
        $this->touchAuditFields();
        $this->save(false, ['failed_login_attempts', 'locked', 'blocked_until', 'updatedate', 'updatetime', 'updateuser']);
    }

    private function touchAuditFields(): void
    {
        $this->updatedate = date('Y-m-d');
        $this->updatetime = date('H:i:s');

        if (!Yii::$app->user->isGuest && Yii::$app->user->identity !== null) {
            $this->updateuser = (int) Yii::$app->user->identity->id;
            return;
        }

        $this->updateuser = (int) $this->id;
    }
}
