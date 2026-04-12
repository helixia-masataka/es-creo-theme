<?php
require_once('../../../wp-load.php');
$taxonomies = get_object_taxonomies('works');
print_r($taxonomies);
?>
