<?php
// Render links in the administrator navigation.
// Keep helper arguments strictly typed.
declare(strict_types=1);
// Print links to the main admin sections.
function admin_nav(): void {
?><nav class="admin-nav"><a href="<?=url('admin/dashboard.php')?>">Dashboard</a><a href="<?=url('admin/brands.php')?>">Brands</a><a href="<?=url('admin/promotions.php')?>">Promotions / Events</a><a href="<?=url('admin/orders.php')?>">Orders</a><a href="<?=url('admin/customers.php')?>">Customers</a><a href="<?=url('admin/team.php')?>">Administrators</a><a href="<?=url('admin/settings.php')?>">Settings</a></nav><?php
// End the navigation helper.
}
