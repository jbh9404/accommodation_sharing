<?php
global $wp_query;
$wp_query->is_404 = FALSE;
$tutorial = $wp_query->query['tutorial'];
get_header();
?>

<div class="wrap">
	custom page <?php echo $tutorial; ?>
</div>

<?php
get_footer();