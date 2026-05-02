(function () {
	'use strict';

	var header = document.querySelector('.site-header');
	var menuToggle = document.querySelector('.menu-toggle');
	var siteNav = document.querySelector('.site-nav');
	if (header && menuToggle && siteNav) {
		function setMenu(open) {
			header.classList.toggle('menu-open', open);
			document.body.classList.toggle('nav-open', open);
			menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			menuToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
		}

		menuToggle.addEventListener('click', function () {
			setMenu(!header.classList.contains('menu-open'));
		});

		siteNav.addEventListener('click', function (event) {
			if (event.target.tagName === 'A') {
				setMenu(false);
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				setMenu(false);
			}
		});
	}

	var input = document.querySelector('[data-cabin-grid]') ? document.querySelector('#cabin-filter') : null;
	if (input) {
		var cards = Array.prototype.slice.call(document.querySelectorAll('[data-cabin-card]'));
		var count = document.querySelector('[data-result-count]');
		var empty = document.querySelector('[data-no-results]');

		function updateCabins() {
			var query = input.value.trim().toLowerCase();
			var visible = 0;

			cards.forEach(function (card) {
				var haystack = [
					card.getAttribute('data-title') || '',
					card.getAttribute('data-location') || '',
					card.getAttribute('data-price') || ''
				].join(' ');
				var match = haystack.indexOf(query) !== -1;

				card.classList.toggle('hidden', !match);
				if (match) {
					visible += 1;
				}
			});

			if (count) {
				count.textContent = visible + (visible === 1 ? ' cabin' : ' cabins');
			}
			if (empty) {
				empty.classList.toggle('hidden', visible !== 0);
			}
		}

		var params = new URLSearchParams(window.location.search);
		var q = params.get('q');
		if (q) {
			input.value = q;
		}

		input.addEventListener('input', updateCabins);
		updateCabins();
	}

	var slider = document.querySelector('[data-hero-slider]');
	var showHeroSlide = null;
	if (slider) {
		var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-hero-slide]'));
		var previous = slider.querySelector('[data-hero-prev]');
		var next = slider.querySelector('[data-hero-next]');
		var count = slider.querySelector('[data-hero-count]');
		var active = 0;

		showHeroSlide = function (index) {
			if (!slides.length) {
				return;
			}
			active = (index + slides.length) % slides.length;
			slides.forEach(function (slide, slideIndex) {
				slide.classList.toggle('is-active', slideIndex === active);
			});
			if (count) {
				count.textContent = (active + 1) + ' / ' + slides.length;
			}
		};

		if (previous) {
			previous.addEventListener('click', function () {
				showHeroSlide(active - 1);
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				showHeroSlide(active + 1);
			});
		}
		var heroTouchStart = 0;
		slider.addEventListener('touchstart', function (event) {
			heroTouchStart = event.touches[0].clientX;
		}, { passive: true });
		slider.addEventListener('touchend', function (event) {
			var delta = event.changedTouches[0].clientX - heroTouchStart;
			if (Math.abs(delta) < 42) {
				return;
			}
			showHeroSlide(active + (delta < 0 ? 1 : -1));
		}, { passive: true });
		showHeroSlide(0);
	}

	var triggers = Array.prototype.slice.call(document.querySelectorAll('.interior-lightbox-trigger'));
	if (!triggers.length) {
		return;
	}

	var lightbox = document.createElement('div');
	lightbox.className = 'image-lightbox';
	lightbox.setAttribute('role', 'dialog');
	lightbox.setAttribute('aria-modal', 'true');
	lightbox.setAttribute('aria-label', 'Full size cabin image');

	var close = document.createElement('button');
	close.type = 'button';
	close.className = 'image-lightbox-close';
	close.setAttribute('aria-label', 'Close image');
	close.innerHTML = '&times;';

	var lightboxPrevious = document.createElement('button');
	lightboxPrevious.type = 'button';
	lightboxPrevious.className = 'image-lightbox-nav previous';
	lightboxPrevious.setAttribute('aria-label', 'Previous image');
	lightboxPrevious.innerHTML = '&lsaquo;';

	var lightboxNext = document.createElement('button');
	lightboxNext.type = 'button';
	lightboxNext.className = 'image-lightbox-nav next';
	lightboxNext.setAttribute('aria-label', 'Next image');
	lightboxNext.innerHTML = '&rsaquo;';

	var image = document.createElement('img');
	lightbox.appendChild(close);
	lightbox.appendChild(lightboxPrevious);
	lightbox.appendChild(image);
	lightbox.appendChild(lightboxNext);
	document.body.appendChild(lightbox);

	var lightboxIndex = 0;

	function showLightboxImage(index) {
		lightboxIndex = (index + triggers.length) % triggers.length;
		var trigger = triggers[lightboxIndex];
		image.src = trigger.getAttribute('data-full-image');
		image.alt = trigger.getAttribute('data-image-alt') || '';
		if (showHeroSlide) {
			showHeroSlide(lightboxIndex);
		}
	}

	function openLightbox(index) {
		showLightboxImage(index);
		lightbox.classList.add('is-open');
		document.body.style.overflow = 'hidden';
		close.focus();
	}

	function closeLightbox() {
		lightbox.classList.remove('is-open');
		document.body.style.overflow = '';
	}

	triggers.forEach(function (trigger) {
		trigger.addEventListener('click', function () {
			openLightbox(triggers.indexOf(trigger));
		});
	});

	close.addEventListener('click', closeLightbox);
	lightboxPrevious.addEventListener('click', function (event) {
		event.stopPropagation();
		showLightboxImage(lightboxIndex - 1);
	});
	lightboxNext.addEventListener('click', function (event) {
		event.stopPropagation();
		showLightboxImage(lightboxIndex + 1);
	});
	lightbox.addEventListener('click', function (event) {
		if (event.target === lightbox) {
			closeLightbox();
		}
	});
	var lightboxTouchStart = 0;
	lightbox.addEventListener('touchstart', function (event) {
		lightboxTouchStart = event.touches[0].clientX;
	}, { passive: true });
	lightbox.addEventListener('touchend', function (event) {
		if (!lightbox.classList.contains('is-open')) {
			return;
		}
		var delta = event.changedTouches[0].clientX - lightboxTouchStart;
		if (Math.abs(delta) < 42) {
			return;
		}
		showLightboxImage(lightboxIndex + (delta < 0 ? 1 : -1));
	}, { passive: true });
	document.addEventListener('keydown', function (event) {
		if (!lightbox.classList.contains('is-open')) {
			return;
		}
		if (event.key === 'Escape') {
			closeLightbox();
		}
		if (event.key === 'ArrowLeft') {
			showLightboxImage(lightboxIndex - 1);
		}
		if (event.key === 'ArrowRight') {
			showLightboxImage(lightboxIndex + 1);
		}
	});
})();
