<?php
$location = hyttehub_custom($page, 'location', 'Location available on request');
$price = hyttehub_custom($page, 'price', 'Price on request');
$guests = hyttehub_custom($page, 'guests');
$bedrooms = hyttehub_custom($page, 'bedrooms');
$amenities = hyttehub_custom($page, 'amenities');
$interiorImage1 = hyttehub_custom($page, 'interior_image_1');
$interiorImage2 = hyttehub_custom($page, 'interior_image_2');
$ownerEmail = hyttehub_custom($page, 'owner_email');
$interiorImages = array();
foreach (array($interiorImage1, $interiorImage2) as $imageField) {
	foreach (explode(',', (string) $imageField) as $imageUrl) {
		$imageUrl = trim($imageUrl);
		if ($imageUrl !== '' && !in_array($imageUrl, $interiorImages, true)) {
			$interiorImages[] = $imageUrl;
		}
	}
}
$heroImages = array_values(array_unique(array_merge(array(hyttehub_cover($page)), $interiorImages)));
?>

<article class="single-cabin">
	<div class="single-cover hero-slider" data-hero-slider>
		<div class="hero-slider-track">
			<?php foreach ($heroImages as $index => $imageUrl) : ?>
				<button class="hero-slide interior-lightbox-trigger<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" data-hero-slide data-lightbox-index="<?php echo $index; ?>" data-full-image="<?php echo hyttehub_e($imageUrl); ?>" data-image-alt="<?php echo hyttehub_e($page->title() . ' photo ' . ($index + 1)); ?>">
					<img src="<?php echo hyttehub_e($imageUrl); ?>" alt="<?php echo hyttehub_e($page->title() . ' photo ' . ($index + 1)); ?>">
				</button>
			<?php endforeach; ?>
		</div>
		<?php if (count($heroImages) > 1) : ?>
			<button class="hero-slider-nav previous" type="button" data-hero-prev aria-label="Previous cabin photo">&lsaquo;</button>
			<button class="hero-slider-nav next" type="button" data-hero-next aria-label="Next cabin photo">&rsaquo;</button>
			<div class="hero-slider-count" data-hero-count>1 / <?php echo count($heroImages); ?></div>
		<?php endif; ?>
	</div>

	<div class="single-layout">
		<div class="single-main">
			<a class="back-link" href="<?php echo hyttehub_site_url('cabins'); ?>">Back to cabins</a>
			<h1><?php echo hyttehub_e($page->title()); ?></h1>
			<p class="single-location"><?php echo hyttehub_e($location); ?></p>

			<div class="rich-content">
				<?php echo $page->content(); ?>
			</div>

		</div>

		<aside class="booking-panel" aria-label="Cabin details">
			<div class="price-line"><?php echo hyttehub_e($price); ?></div>
			<dl class="detail-list">
				<?php if ($guests !== '') : ?>
					<div><dt>Guests</dt><dd><?php echo hyttehub_e($guests); ?></dd></div>
				<?php endif; ?>
				<?php if ($bedrooms !== '') : ?>
					<div><dt>Bedrooms</dt><dd><?php echo hyttehub_e($bedrooms); ?></dd></div>
				<?php endif; ?>
				<?php if ($amenities !== '') : ?>
					<div class="wide"><dt>Amenities</dt><dd><?php echo hyttehub_e($amenities); ?></dd></div>
				<?php endif; ?>
			</dl>

			<?php if ($ownerEmail !== '') : ?>
				<a class="button primary full" href="mailto:<?php echo hyttehub_e($ownerEmail); ?>?subject=<?php echo rawurlencode('Cabin inquiry: ' . $page->title()); ?>">Contact owner</a>
			<?php else : ?>
				<p class="muted">Owner contact details are not available yet.</p>
			<?php endif; ?>
		</aside>
	</div>
</article>
