<?php
require_once('../../../wp-load.php');
$post_types = get_post_types(array('_builtin' => false), 'names');
print_r($post_types);
?>
