/**
 * Package page: media slider with thumbnails, slide counter, and pausing
 * videos when the slide changes.
 */
(function () {
	'use strict';

	if (!window.Swiper || !document.querySelector('.gMain')) return;

	var counter = document.querySelector('.gCount');
	var thumbs = document.querySelector('.gThumbs')
		? new Swiper('.gThumbs', { slidesPerView: 'auto', spaceBetween: 10, freeMode: true, watchSlidesProgress: true })
		: null;

	new Swiper('.gMain', {
		spaceBetween: 10,
		navigation: { nextEl: '.gMain .swiper-button-next', prevEl: '.gMain .swiper-button-prev' },
		pagination: { el: '.gMain .swiper-pagination', clickable: true },
		thumbs: thumbs ? { swiper: thumbs } : undefined,
		on: {
			slideChange: function () {
				if (counter) counter.textContent = (this.realIndex + 1) + ' / ' + this.slides.length;
				document.querySelectorAll('.gMain video').forEach(function (v) { v.pause(); });
			}
		}
	});
})();
