<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $document */
/** @var array $mailDefaults */

echo $this->render('//doc-tire-shared/_send_mail_content', compact('config', 'document', 'mailDefaults'));