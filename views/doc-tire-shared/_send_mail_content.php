<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $document */
/** @var array $mailDefaults */

use yii\helpers\Html;
?>
<form id="doc-tire-send-mail-form" data-docentry="<?= (int) ($document['docentry'] ?? 0) ?>">
    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Correo destino</label>
            <input type="email" class="form-control" name="email" placeholder="destino@empresa.com" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Asunto</label>
            <input type="text" class="form-control" name="subject" value="<?= Html::encode((string) ($mailDefaults['subject'] ?? '')) ?>" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Mensaje</label>
            <textarea class="form-control" name="message" rows="5" required><?= Html::encode((string) ($mailDefaults['message'] ?? '')) ?></textarea>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success">Enviar correo</button>
    </div>
</form>