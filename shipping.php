<?php
// Display the shop's shipping policy text.
// Load saved shop settings.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
// Open the translated shipping page.
page_start(tr('shipping'));
?><div class="page-head"><div class="container"><h1><?=h(tr('shipping'))?></h1></div></div><main class="container section"><div class="panel"><p><?=nl2br(h(setting('shipping_text')))?></p></div></main><?php
// Close the shared customer layout.
page_end();
