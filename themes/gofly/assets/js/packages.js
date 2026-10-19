/**
 * Packages archive: instant filters (category, destination, duration, price),
 * sorting, client-side pagination and the mobile off-canvas sidebar.
 * Reads ?dest=slug and ?cat=slug so destination links open pre-filtered.
 */
(function () {
	'use strict';

	var wrap = document.querySelector('[data-packages]');
	if (!wrap) return;

	var $ = function (sel) { return wrap.querySelector(sel); };
	var $$ = function (sel) { return Array.prototype.slice.call(wrap.querySelectorAll(sel)); };
	var grid = $('[data-pf-items]');
	var cards = Array.prototype.slice.call(grid.children);
	var range = $('[data-filter-price]');
	var pager = $('[data-pf-pages]');
	var empty = $('[data-pf-empty]');
	var perPage = parseInt(wrap.getAttribute('data-per-page'), 10) || 9;
	var defaults = { cat: 'all', dest: 'all', dur: 'all', sort: 'new', max: range ? +range.max : Infinity, page: 1 };
	var state = Object.assign({}, defaults);

	cards.forEach(function (card, i) { card.dataset.index = i; });

	var params = new URLSearchParams(location.search);
	if (params.get('dest')) state.dest = params.get('dest');
	if (params.get('cat')) state.cat = params.get('cat');

	function inDuration(days) {
		switch (state.dur) {
			case '3-5': return days >= 3 && days <= 5;
			case '6-7': return days >= 6 && days <= 7;
			case '8-': return days >= 8;
			default: return true;
		}
	}

	function matches(card) {
		var d = card.dataset;
		var price = +d.price;
		return (state.cat === 'all' || d.cat.split(' ').indexOf(state.cat) > -1) &&
			(state.dest === 'all' || d.dest.split(' ').indexOf(state.dest) > -1) &&
			inDuration(+d.days) &&
			(!price || price <= state.max);
	}

	function compare(a, b) {
		var pa = +a.dataset.price || Infinity, pb = +b.dataset.price || Infinity;
		switch (state.sort) {
			case 'low': return pa - pb;
			case 'high': return (+b.dataset.price || 0) - (+a.dataset.price || 0);
			case 'dur': return (+a.dataset.days || 99) - (+b.dataset.days || 99);
			default: return a.dataset.index - b.dataset.index;
		}
	}

	function syncButtons(selector, value) {
		$$(selector + ' button').forEach(function (btn) {
			var on = btn.dataset.value === value;
			btn.classList.toggle('is-active', on);
			btn.setAttribute(btn.getAttribute('role') === 'radio' ? 'aria-checked' : 'aria-pressed', String(on));
		});
	}

	function renderPager(pages) {
		pager.hidden = pages < 2;
		if (pages < 2) { pager.innerHTML = ''; return; }
		var icon = function (n) { return '<span class="ms" aria-hidden="true">' + n + '</span>'; };
		var html = '<button type="button" data-page="' + (state.page - 1) + '" aria-label="الصفحة السابقة"' + (state.page === 1 ? ' disabled' : '') + '>' + icon('chevron_right') + '</button>';
		for (var p = 1; p <= pages; p++) {
			html += '<button type="button" data-page="' + p + '"' + (p === state.page ? ' aria-current="page"' : '') + '>' + p + '</button>';
		}
		html += '<button type="button" data-page="' + (state.page + 1) + '" aria-label="الصفحة التالية"' + (state.page === pages ? ' disabled' : '') + '>' + icon('chevron_left') + '</button>';
		pager.innerHTML = html;
	}

	function render() {
		var list = cards.filter(matches).sort(compare);
		var pages = Math.max(1, Math.ceil(list.length / perPage));
		state.page = Math.min(state.page, pages);
		var start = (state.page - 1) * perPage;

		cards.forEach(function (card) { card.hidden = true; });
		list.forEach(function (card, i) {
			card.hidden = i < start || i >= start + perPage;
			grid.appendChild(card); // keeps DOM order = sort order
		});

		$$('[data-pf-count]').forEach(function (el) { el.textContent = list.length; });
		if (empty) empty.hidden = list.length > 0;
		if (range) {
			range.value = state.max;
			$('[data-pf-max]').textContent = (+state.max).toLocaleString('en-US');
		}
		syncButtons('[data-filter="cat"]', state.cat);
		syncButtons('[data-filter="dest"]', state.dest);
		syncButtons('[data-filter="dur"]', state.dur);
		syncButtons('[data-sort]', state.sort);
		renderPager(pages);
	}

	$$('[data-filter]').forEach(function (group) {
		group.addEventListener('click', function (e) {
			var btn = e.target.closest('button');
			if (!btn) return;
			state[group.dataset.filter] = btn.dataset.value;
			state.page = 1;
			render();
		});
	});

	$('[data-sort]').addEventListener('click', function (e) {
		var btn = e.target.closest('button');
		if (!btn) return;
		state.sort = btn.dataset.value;
		state.page = 1;
		render();
	});

	if (range) {
		range.addEventListener('input', function () {
			state.max = +range.value;
			state.page = 1;
			render();
		});
	}

	pager.addEventListener('click', function (e) {
		var btn = e.target.closest('button[data-page]');
		if (!btn || btn.disabled) return;
		state.page = +btn.dataset.page;
		render();
		grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
	});

	$$('[data-pf-reset]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			state = Object.assign({}, defaults);
			render();
		});
	});

	/* Mobile off-canvas */
	function setSidebar(open) {
		document.documentElement.classList.toggle('is-pf-open', open);
		document.body.style.overflow = open ? 'hidden' : '';
	}
	$$('[data-pf-open]').forEach(function (b) { b.addEventListener('click', function () { setSidebar(true); }); });
	$$('[data-pf-close]').forEach(function (b) { b.addEventListener('click', function () { setSidebar(false); }); });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setSidebar(false); });

	render();
})();
