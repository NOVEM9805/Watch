<?php
// Display the shop's returns and warranty text.
// Load saved shop settings.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
// Open the translated returns page.
page_start(tr('returns'));
?><div class="page-head"><div class="container"><h1><?=h(tr('returns'))?></h1></div></div><main class="container section"><div class="panel"><p><?=nl2br(h(setting('returns_text')))?></p></div></main><?php
// Close the shared customer layout.
page_end();
