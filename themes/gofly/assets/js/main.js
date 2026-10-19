/**
 * Go Fly — site-wide behaviour: mobile menu, dialogs, scroll reveals, counters.
 */
(function () {
	'use strict';

	var doc = document;
	var root = doc.documentElement;
	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); };

	/* Mobile menu ------------------------------------------------------- */
	var mnav = doc.getElementById('mnav');
	var toggle = doc.querySelector('.mnav-toggle');

	function setMenu(open) {
		if (!mnav) return;
		root.classList.toggle('is-mnav-open', open);
		mnav.setAttribute('aria-hidden', String(!open));
		if (toggle) toggle.setAttribute('aria-expanded', String(open));
		doc.body.style.overflow = open ? 'hidden' : '';
	}

	if (toggle) toggle.addEventListener('click', function () { setMenu(true); });
	$$('[data-mnav-close]').forEach(function (el) { el.addEventListener('click', function () { setMenu(false); }); });
	doc.addEventListener('keydown', function (e) { if (e.key === 'Escape') setMenu(false); });

	/* Dialogs: quote modal + hero video -------------------------------- */
	function openDialog(dialog) {
		if (!dialog) return;
		var tpl = dialog.querySelector('template');
		var frame = dialog.querySelector('.video-dialog__frame');
		if (tpl && frame && !frame.children.length) frame.appendChild(tpl.content.cloneNode(true));
		dialog.showModal();
	}

	function closeDialog(dialog) {
		var frame = dialog.querySelector('.video-dialog__frame');
		if (frame) frame.innerHTML = ''; // stops playback
		dialog.close();
	}

	$$('[data-modal-open]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			openDialog(doc.querySelector('[data-modal="' + btn.getAttribute('data-modal-open') + '"]'));
		});
	});
	$$('[data-video-open]').forEach(function (btn) {
		btn.addEventListener('click', function () { openDialog(doc.querySelector('[data-video-dialog]')); });
	});
	$$('dialog').forEach(function (dialog) {
		dialog.addEventListener('click', function (e) {
			if (e.target === dialog || e.target.closest('[data-modal-close],[data-video-close]')) closeDialog(dialog);
		});
		dialog.addEventListener('cancel', function () {
			var frame = dialog.querySelector('.video-dialog__frame');
			if (frame) frame.innerHTML = '';
		});
	});

	/* Placeholder links should not jump to the top. */
	doc.addEventListener('click', function (e) {
		var a = e.target.closest && e.target.closest('a[href="#"]');
		if (a) e.preventDefault();
	});

	if (reduceMotion || !('IntersectionObserver' in window)) return;

	/* Scroll reveal: below-the-fold blocks fade up once; grid items stagger. */
	var revealIO = new IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			if (entry.isIntersecting) {
				entry.target.classList.add('in');
				revealIO.unobserve(entry.target);
			}
		});
	}, { rootMargin: '0px 0px -6% 0px', threshold: 0.05 });

	window.goflyReveal = function (els) {
		els.forEach(function (el) {
			if (el.getBoundingClientRect().top < window.innerHeight) return;
			el.classList.add('rv');
			revealIO.observe(el);
		});
	};

	var targets = [];
	$$('.site-main > .section, .pkg-content, .post-layout').forEach(function (block) {
		Array.prototype.forEach.call(block.children, function (child) {
			if (child.classList.contains('grid')) {
				Array.prototype.forEach.call(child.children, function (item, i) {
					item.style.setProperty('--d', (i % 4) * 0.08 + 's');
					targets.push(item);
				});
			} else {
				targets.push(child);
			}
		});
	});
	window.goflyReveal(targets.concat($$('.booking-card')));

	/* Count-up numbers (+12, 98%, +12,000). Final value stays in the HTML. */
	var countIO = new IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			if (!entry.isIntersecting) return;
			countIO.unobserve(entry.target);
			var el = entry.target;
			var m = el.textContent.trim().match(/^(\+?)([\d,]+)(%?)$/);
			if (!m) return;
			var end = +m[2].replace(/,/g, '');
			var big = end >= 1000;
			var t0 = performance.now();
			el.style.minWidth = el.offsetWidth + 'px';
			(function step(now) {
				var k = Math.min(1, (now - t0) / 1800);
				var v = Math.round(end * (1 - Math.pow(1 - k, 3)));
				el.textContent = m[1] + (big ? v.toLocaleString('en-US') : v) + m[3];
				if (k < 1) requestAnimationFrame(step);
			})(t0);
		});
	}, { threshold: 0.6 });
	$$('[data-count]').forEach(function (el) { countIO.observe(el); });
})();
