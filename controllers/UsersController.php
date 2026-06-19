<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\system\User;
use app\models\system\UserSearch;
use app\models\system\UserGroup;
use app\components\MenuService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\helpers\ArrayHelper;

/**
 * UsersController — CRUD de usuarios del sistema.
 *
 * RBAC:
 *   users.view   → index, view
 *   users.create → create
 *   users.update → update, toggle-active
 *   users.delete → delete
 */
class UsersController extends Controller
{
    // public string $layout = 'main';

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'delete'        => ['POST'],
                    'toggle-active' => ['POST'],
                    'list-ajax' => ['GET'],
                    'get-ajax' => ['GET'],
                    'save-ajax' => ['POST'],
                    'delete-ajax' => ['POST'],
                    'groups-list-ajax' => ['GET'],
                    'group-get-ajax' => ['GET'],
                    'group-save-ajax' => ['POST'],
                    'group-delete-ajax' => ['POST'],
                    'rbac-catalog-ajax' => ['GET'],
                    'assignments-ajax' => ['GET'],
                    'save-assignments-ajax' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'allow'   => true,
                        'roles'   => ['users.view'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow'   => true,
                        'roles'   => ['users.create'],
                    ],
                    [
                        'actions' => ['update', 'toggle-active'],
                        'allow'   => true,
                        'roles'   => ['users.update'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow'   => true,
                        'roles'   => ['users.delete'],
                    ],
                    [
                        'actions' => [
                            'list-ajax', 'get-ajax', 'save-ajax', 'delete-ajax',
                            'groups-list-ajax', 'group-get-ajax', 'group-save-ajax', 'group-delete-ajax',
                            'rbac-catalog-ajax', 'assignments-ajax', 'save-assignments-ajax'
                        ],
                        'allow' => true,
                        'roles' => ['config.users'],
                    ],
                ],
            ],
        ];
    }

    // ─── AJAX Admin (Config/Users) ─────────────────────────────────────────

    public function actionListAjax(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $rows = User::find()
                ->alias('u')
                ->select(['u.*', 'g.name AS group_name'])
                ->leftJoin('{{%user_groups}} g', 'g.id = u.group_id')
                ->orderBy(['u.id' => SORT_ASC])
                ->asArray()
                ->all();

            return $this->asJson(['Success' => 'Ok', 'Msg' => '', 'Data' => $rows]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGetAjax(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = User::findOne($id);
        if ($model === null) {
            return $this->asJson(['Success' => 'Error', 'Msg' => 'Usuario no encontrado', 'Data' => []]);
        }

        $data = $model->toArray();
        unset($data['password_hash'], $data['auth_key'], $data['access_token'], $data['token_reset_password']);

        return $this->asJson(['Success' => 'Ok', 'Msg' => '', 'Data' => $data]);
    }

    public function actionSaveAjax(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $payload = Yii::$app->request->post();
            $id = isset($payload['id']) && $payload['id'] !== '' ? (int)$payload['id'] : null;
            $isNew = $id === null;

            $model = $isNew ? new User() : User::findOne($id);
            if ($model === null) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Usuario no encontrado', 'Data' => []]);
            }

            $model->scenario = $isNew ? User::SCENARIO_CREATE : User::SCENARIO_UPDATE;
            $model->code = trim((string)($payload['code'] ?? $model->code ?? ''));
            $model->username = trim((string)($payload['username'] ?? $model->username ?? ''));
            $model->name = trim((string)($payload['name'] ?? $model->name ?? ''));
            $model->last_name = trim((string)($payload['last_name'] ?? ''));
            $model->email = trim((string)($payload['email'] ?? ''));
            $model->phone = trim((string)($payload['phone'] ?? ''));
            $model->group_id = ($payload['group_id'] ?? '') !== '' ? (int)$payload['group_id'] : null;
            $model->active = (int)(($payload['active'] ?? '1') === '1' ? 1 : 0);

            if ($model->code === '') {
                $model->code = 'USR-' . strtoupper(bin2hex(random_bytes(3)));
            }

            $password = (string)($payload['password'] ?? '');
            if ($isNew && $password === '') {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'La contraseña es obligatoria para crear usuario', 'Data' => []]);
            }
            if ($password !== '') {
                $model->setPassword($password);
                if ($isNew) {
                    $model->generateAuthKey();
                }
            }

            if (!$model->validate()) {
                $errs = [];
                foreach ($model->getErrors() as $fieldErrs) {
                    $errs = array_merge($errs, $fieldErrs);
                }
                return $this->asJson(['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []]);
            }

            if (!$model->save(false)) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'No se pudo guardar usuario', 'Data' => []]);
            }

            return $this->asJson(['Success' => 'Ok', 'Msg' => 'Usuario guardado', 'Data' => ['id' => $model->id]]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionDeleteAjax(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $id = (int)Yii::$app->request->post('id');
            if ($id <= 0) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'ID inválido', 'Data' => []]);
            }
            if ($id === (int)Yii::$app->user->id) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'No puedes eliminar tu propio usuario', 'Data' => []]);
            }

            $model = User::findOne($id);
            if ($model === null) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Usuario no encontrado', 'Data' => []]);
            }

            $model->delete();
            return $this->asJson(['Success' => 'Ok', 'Msg' => 'Usuario eliminado', 'Data' => []]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGroupsListAjax(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $rows = UserGroup::find()
                ->alias('g')
                ->select([
                    'g.*',
                    'users_count' => '(SELECT COUNT(1) FROM {{%users}} u WHERE u.group_id = g.id)'
                ])
                ->orderBy(['g.id' => SORT_ASC])
                ->asArray()
                ->all();

            return $this->asJson(['Success' => 'Ok', 'Msg' => '', 'Data' => $rows]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGroupGetAjax(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = UserGroup::findOne($id);
        if ($model === null) {
            return $this->asJson(['Success' => 'Error', 'Msg' => 'Grupo no encontrado', 'Data' => []]);
        }
        return $this->asJson(['Success' => 'Ok', 'Msg' => '', 'Data' => $model->toArray()]);
    }

    public function actionGroupSaveAjax(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $payload = Yii::$app->request->post();
            $id = isset($payload['id']) && $payload['id'] !== '' ? (int)$payload['id'] : null;
            $isNew = $id === null;

            $model = $isNew ? new UserGroup() : UserGroup::findOne($id);
            if ($model === null) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Grupo no encontrado', 'Data' => []]);
            }

            $model->name = trim((string)($payload['name'] ?? ''));
            $model->description = trim((string)($payload['description'] ?? ''));
            $model->active = (int)(($payload['active'] ?? '1') === '1' ? 1 : 0);

            if (!$model->validate()) {
                $errs = [];
                foreach ($model->getErrors() as $fieldErrs) {
                    $errs = array_merge($errs, $fieldErrs);
                }
                return $this->asJson(['Success' => 'Error', 'Msg' => implode('; ', $errs), 'Data' => []]);
            }

            if (!$model->save(false)) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'No se pudo guardar grupo', 'Data' => []]);
            }

            return $this->asJson(['Success' => 'Ok', 'Msg' => 'Grupo guardado', 'Data' => ['id' => $model->id]]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionGroupDeleteAjax(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $id = (int)Yii::$app->request->post('id');
            if ($id <= 0) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'ID inválido', 'Data' => []]);
            }
            $model = UserGroup::findOne($id);
            if ($model === null) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'Grupo no encontrado', 'Data' => []]);
            }
            $userCount = (int)User::find()->where(['group_id' => $id])->count();
            if ($userCount > 0) {
                return $this->asJson(['Success' => 'Error', 'Msg' => 'No se puede eliminar: hay usuarios asociados al grupo', 'Data' => []]);
            }

            Yii::$app->db->createCommand()->delete('{{%group_rbac_assignment}}', ['group_id' => $id])->execute();
            $model->delete();
            return $this->asJson(['Success' => 'Ok', 'Msg' => 'Grupo eliminado', 'Data' => []]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionRbacCatalogAjax(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $items = (new \yii\db\Query())
                ->from('{{%auth_item}}')
                ->select(['name', 'type', 'description'])
                ->where(['in', 'type', [1, 2]])
                ->orderBy(['type' => SORT_ASC, 'name' => SORT_ASC])
                ->all();

            $roles = [];
            $permissions = [];
            foreach ($items as $item) {
                if ((int)$item['type'] === 1) {
                    $roles[] = $item;
                } else {
                    $permissions[] = $item;
                }
            }

            return $this->asJson([
                'Success' => 'Ok',
                'Msg' => '',
                'Data' => ['roles' => $roles, 'permissions' => $permissions],
            ]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionAssignmentsAjax(string $subjectType, int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            if ($subjectType === 'user') {
                $user = User::findOne($id);
                if ($user === null) {
                    return $this->asJson(['Success' => 'Error', 'Msg' => 'Usuario no encontrado', 'Data' => []]);
                }
                $assigned = (new \yii\db\Query())
                    ->from('{{%auth_assignment}}')
                    ->select(['item_name'])
                    ->where(['user_id' => (string)$id])
                    ->column();

                return $this->asJson(['Success' => 'Ok', 'Msg' => '', 'Data' => ['items' => $assigned]]);
            }

            if ($subjectType === 'group') {
                $group = UserGroup::findOne($id);
                if ($group === null) {
                    return $this->asJson(['Success' => 'Error', 'Msg' => 'Grupo no encontrado', 'Data' => []]);
                }
                $assigned = (new \yii\db\Query())
                    ->from('{{%group_rbac_assignment}}')
                    ->select(['item_name'])
                    ->where(['group_id' => $id])
                    ->column();

                return $this->asJson(['Success' => 'Ok', 'Msg' => '', 'Data' => ['items' => $assigned]]);
            }

            return $this->asJson(['Success' => 'Error', 'Msg' => 'subjectType inválido', 'Data' => []]);
        } catch (\Throwable $e) {
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    public function actionSaveAssignmentsAjax(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->user->can('rbac.manage')) {
            return $this->asJson(['Success' => 'Error', 'Msg' => 'Sin permiso para administrar RBAC', 'Data' => []]);
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $subjectType = (string)Yii::$app->request->post('subjectType', '');
            $id = (int)Yii::$app->request->post('id', 0);
            $items = Yii::$app->request->post('items', []);
            if (!is_array($items)) {
                $items = [];
            }
            $items = array_values(array_unique(array_filter(array_map('strval', $items))));

            $catalog = (new \yii\db\Query())
                ->from('{{%auth_item}}')
                ->select(['name'])
                ->where(['in', 'type', [1, 2]])
                ->column();
            $validMap = array_flip($catalog);
            $items = array_values(array_filter($items, static fn($name) => isset($validMap[$name])));

            if ($subjectType === 'user') {
                $user = User::findOne($id);
                if ($user === null) {
                    $tx->rollBack();
                    return $this->asJson(['Success' => 'Error', 'Msg' => 'Usuario no encontrado', 'Data' => []]);
                }

                Yii::$app->db->createCommand()->delete('{{%auth_assignment}}', ['user_id' => (string)$id])->execute();
                foreach ($items as $item) {
                    Yii::$app->db->createCommand()->insert('{{%auth_assignment}}', [
                        'item_name' => $item,
                        'user_id' => (string)$id,
                        'created_at' => time(),
                    ])->execute();
                }

                $tx->commit();
                return $this->asJson(['Success' => 'Ok', 'Msg' => 'Permisos del usuario actualizados', 'Data' => []]);
            }

            if ($subjectType === 'group') {
                $group = UserGroup::findOne($id);
                if ($group === null) {
                    $tx->rollBack();
                    return $this->asJson(['Success' => 'Error', 'Msg' => 'Grupo no encontrado', 'Data' => []]);
                }

                $oldItems = (new \yii\db\Query())
                    ->from('{{%group_rbac_assignment}}')
                    ->select(['item_name'])
                    ->where(['group_id' => $id])
                    ->column();
                $toRemove = array_values(array_diff($oldItems, $items));
                $toAdd = array_values(array_diff($items, $oldItems));

                Yii::$app->db->createCommand()->delete('{{%group_rbac_assignment}}', ['group_id' => $id])->execute();
                foreach ($items as $item) {
                    Yii::$app->db->createCommand()->insert('{{%group_rbac_assignment}}', [
                        'group_id' => $id,
                        'item_name' => $item,
                        'created_at' => time(),
                    ])->execute();
                }

                $userIds = User::find()->select(['id'])->where(['group_id' => $id])->column();
                foreach ($userIds as $uid) {
                    foreach ($toRemove as $item) {
                        Yii::$app->db->createCommand()->delete('{{%auth_assignment}}', [
                            'user_id' => (string)$uid,
                            'item_name' => $item,
                        ])->execute();
                    }
                    foreach ($toAdd as $item) {
                        $exists = (new \yii\db\Query())
                            ->from('{{%auth_assignment}}')
                            ->where(['user_id' => (string)$uid, 'item_name' => $item])
                            ->exists();
                        if (!$exists) {
                            Yii::$app->db->createCommand()->insert('{{%auth_assignment}}', [
                                'item_name' => $item,
                                'user_id' => (string)$uid,
                                'created_at' => time(),
                            ])->execute();
                        }
                    }
                }

                $tx->commit();
                return $this->asJson(['Success' => 'Ok', 'Msg' => 'Permisos del grupo actualizados', 'Data' => []]);
            }

            $tx->rollBack();
            return $this->asJson(['Success' => 'Error', 'Msg' => 'subjectType inválido', 'Data' => []]);
        } catch (\Throwable $e) {
            $tx->rollBack();
            return $this->asJson(['Success' => 'Error', 'Msg' => $e->getMessage(), 'Data' => []]);
        }
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function actionIndex(): string
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // ─── View ─────────────────────────────────────────────────────────────────

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function actionCreate(): Response|string
    {
        $model           = new User();
        $model->scenario = User::SCENARIO_CREATE;
        $model->active   = 1;

        if ($model->load(Yii::$app->request->post())) {
            // Generar auth_key si no existe
            $model->generateAuthKey();

            // Hashear contraseña
            if ($model->password !== null && $model->password !== '') {
                $model->setPassword($model->password);
            }

            // Auto-generar code si no se proporcionó
            if (empty($model->code)) {
                $model->code = 'USR-' . strtoupper(bin2hex(random_bytes(3)));
            }

            if ($model->validate() && $model->save(false)) {
                Yii::$app->session->setFlash('success', "Usuario '{$model->username}' creado correctamente.");
                MenuService::invalidateAll(); // el nuevo usuario podría cambiar conteos
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function actionUpdate(int $id): Response|string
    {
        $model           = $this->findModel($id);
        $model->scenario = User::SCENARIO_UPDATE;
        $model->password = null; // no mostrar hash en el campo

        if ($model->load(Yii::$app->request->post())) {
            // Solo re-hashear si se ingresó una contraseña nueva
            if ($model->password !== null && $model->password !== '') {
                $model->setPassword($model->password);
            }

            if ($model->validate() && $model->save(false)) {
                Yii::$app->session->setFlash('success', "Usuario '{$model->username}' actualizado correctamente.");
                MenuService::invalidate((int) $model->id);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function actionDelete(int $id): Response
    {
        $model = $this->findModel($id);

        // No puede eliminarse a sí mismo
        if ($model->id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'No puedes eliminar tu propio usuario.');
            return $this->redirect(['index']);
        }

        $username = $model->username;
        $model->delete();

        MenuService::invalidate($id);
        Yii::$app->session->setFlash('success', "Usuario '{$username}' eliminado.");

        return $this->redirect(['index']);
    }

    // ─── Toggle active ────────────────────────────────────────────────────────

    public function actionToggleActive(int $id): Response
    {
        $model = $this->findModel($id);

        // No puede desactivarse a sí mismo
        if ($model->id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'No puedes desactivar tu propio usuario.');
            return $this->redirect(['index']);
        }

        $model->active = $model->active ? 0 : 1;
        $model->save(false, ['active', 'updated_at']);

        $estado = $model->active ? 'activado' : 'desactivado';
        Yii::$app->session->setFlash('success', "Usuario '{$model->username}' {$estado}.");

        MenuService::invalidate($id);

        return $this->redirect(['index']);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function findModel(int $id): User
    {
        $model = User::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Usuario no encontrado.');
        }
        return $model;
    }
}
