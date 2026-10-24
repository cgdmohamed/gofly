/**
 * Booking page: flight / hotel request widget.
 *
 * Builds a booking.com-style search bar, suggests cities from the free
 * Open-Meteo geocoding API (no key), validates the required fields and opens
 * WhatsApp with a ready-made Arabic message to the company number.
 * Settings come from wp_localize_script → window.goflyBooking.
 */
(function () {
	'use strict';

	var page = document.getElementById('bk-page');
	if (!page) return;

	var cfg = window.goflyBooking || {};
	var WA = cfg.whatsapp || '';
	var BRAND = cfg.brand || '';
	var GEO_API = 'https://geocoding-api.open-meteo.com/v1/search';

	function icon(name) { return '<span class="ms" aria-hidden="true">' + name + '</span>'; }

	function field(id, ic, label, type, attrs, cls) {
		return '<label class="bk-f ' + (cls || '') + '" id="f-' + id + '">' + icon(ic) +
			'<div><small>' + label + '</small><input id="bk-' + id + '" type="' + (type || 'text') + '" ' + (attrs || '') + '></div></label>';
	}

	function stepper(id, label, min, value) {
		return '<div class="bk-r"><div>' + label + '</div><div class="bk-st">' +
			'<button type="button" data-s="-1" data-t="' + id + '" aria-label="إنقاص">−</button>' +
			'<b id="bk-' + id + '" data-min="' + min + '">' + value + '</b>' +
			'<button type="button" data-s="1" data-t="' + id + '" aria-label="زيادة">+</button></div></div>';
	}

	var travellers =
		'<div class="bk-f full" id="f-trv" style="cursor:pointer" tabindex="0" role="button" aria-haspopup="true">' + icon('group') +
		'<div><small>المسافرون</small><span id="bk-trvt" style="font-weight:600">بالغ واحد</span></div>' +
		'<div class="bk-pop" id="bk-pop">' +
		stepper('ad', 'بالغون', 1, 1) +
		stepper('ch', 'أطفال (أقل من 12)', 0, 0) +
		'<div id="bk-roomsr">' + stepper('rooms', 'غرف', 1, 1) + '</div>' +
		'<div class="bk-r" id="bk-clsr"><div>الدرجة</div><select id="bk-cls"><option>اقتصادية</option><option>اقتصادية مميزة</option><option>رجال أعمال</option><option>الأولى</option></select></div>' +
		'<div class="bk-r" id="bk-starr"><div>فئة الفندق</div><select id="bk-stars"><option>حسب التوفر</option><option>3 نجوم</option><option>4 نجوم</option><option>5 نجوم</option></select></div>' +
		'<button class="bk-done" type="button">تم</button></div></div>';

	page.innerHTML =
		'<div class="bk-tabs" role="tablist">' +
			'<button type="button" data-m="flight" class="on">' + icon('flight') + 'طيران</button>' +
			'<button type="button" data-m="hotel">' + icon('hotel') + 'فنادق</button>' +
			'<button type="button" data-m="both">' + icon('luggage') + 'طيران + فندق</button>' +
		'</div>' +
		'<div class="bk-seg" id="bk-oneway" role="radiogroup" aria-label="نوع الرحلة">' +
			'<button type="button" role="radio" data-ow="0" aria-checked="true" class="on">' + icon('sync_alt') + 'ذهاب وعودة</button>' +
			'<button type="button" role="radio" data-ow="1" aria-checked="false">' + icon('arrow_right_alt') + 'ذهاب فقط</button>' +
		'</div>' +
		'<div class="bk-bar" id="bk-bar">' +
			'<div class="bk-h" id="g-city" style="display:contents">' + field('city', 'location_on', 'الوجهة', 'text', 'autocomplete="off" placeholder="إلى أين تريد الذهاب؟" data-ac', 'full') + '</div>' +
			'<div id="g-from" style="display:contents">' + field('from', 'flight_takeoff', 'من', 'text', 'autocomplete="off" placeholder="مدينة المغادرة" data-ac') + '</div>' +
			'<div id="g-to" style="display:contents">' + field('to', 'flight_land', 'إلى', 'text', 'autocomplete="off" placeholder="مدينة الوصول" data-ac') + '</div>' +
			'<div id="g-fd" style="display:contents">' + field('dep', 'calendar_month', 'تاريخ الذهاب', 'date') +
				'<span id="bk-retw" style="display:contents">' + field('ret', 'event', 'تاريخ العودة', 'date') + '</span></div>' +
			'<div id="g-hd" class="bk-h" style="display:contents">' + field('cin', 'calendar_month', 'تسجيل الدخول', 'date') + field('cout', 'event', 'تسجيل الخروج', 'date') + '</div>' +
			travellers +
		'</div>' +
		'<div class="bk-card"><h2>بيانات التواصل</h2><div class="bk-c">' +
			field('name', 'person', 'الاسم', 'text', 'placeholder="اسمك الكامل" autocomplete="name"') +
			field('tel', 'call', 'رقم الجوال', 'tel', 'placeholder="05xxxxxxxx" dir="ltr" style="text-align:right" autocomplete="tel"') +
			field('note', 'chat', 'ملاحظات (اختياري)', 'text', 'placeholder="أي طلبات خاصة"', 'w') +
		'</div>' +
		'<button class="bk-btn" type="button" id="bk-send"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>إرسال الطلب عبر واتساب</button>' +
		'<div class="bk-note">سيُفتح واتساب برسالة جاهزة لإرسالها إلى ' + BRAND + '.</div></div>';

	var mode = 'flight';
	var oneWay = false;
	var $ = function (id) { return document.getElementById('bk-' + id); };
	var byId = function (id) { return document.getElementById(id); };
	var today = new Date().toISOString().slice(0, 10);

	['dep', 'ret', 'cin', 'cout'].forEach(function (id) { $(id).min = today; });

	function show(id, on) {
		byId(id).classList.toggle('bk-h', !on);
		byId(id).style.display = on ? 'contents' : 'none';
	}

	function updateColumns() {
		byId('bk-retw').style.display = oneWay ? 'none' : 'contents';
		byId('bk-bar').style.setProperty('--n', mode === 'hotel' || oneWay ? 4 : 5);
	}

	function setMode(m) {
		mode = m;
		page.querySelectorAll('.bk-tabs button').forEach(function (b) { b.classList.toggle('on', b.dataset.m === m); });
		var flights = m !== 'hotel';
		show('g-from', flights);
		show('g-to', flights);
		show('g-fd', flights);
		show('g-city', !flights);
		show('g-hd', !flights);
		byId('bk-oneway').style.display = flights ? '' : 'none';
		byId('bk-clsr').style.display = flights ? '' : 'none';
		byId('bk-roomsr').style.display = m === 'flight' ? 'none' : '';
		byId('bk-starr').style.display = m === 'flight' ? 'none' : '';
		updateColumns();
		travellerSummary();
	}

	byId('bk-oneway').addEventListener('click', function (e) {
		var btn = e.target.closest('button');
		if (!btn) return;
		oneWay = btn.dataset.ow === '1';
		this.querySelectorAll('button').forEach(function (b) {
			b.classList.toggle('on', b === btn);
			b.setAttribute('aria-checked', String(b === btn));
		});
		updateColumns();
	});

	page.querySelector('.bk-tabs').addEventListener('click', function (e) {
		var btn = e.target.closest('button');
		if (btn) setMode(btn.dataset.m);
	});

	$('dep').addEventListener('change', function () { $('ret').min = this.value || today; });
	$('cin').addEventListener('change', function () { $('cout').min = this.value || today; });
	page.querySelectorAll('.bk-f input[type=date]').forEach(function (input) {
		input.addEventListener('change', function () { input.classList.toggle('has', !!input.value); });
		input.addEventListener('click', function () { try { input.showPicker(); } catch (err) { /* unsupported */ } });
	});

	/* Travellers popover */
	var pop = byId('bk-pop');
	var trv = byId('f-trv');

	function travellerSummary() {
		var adults = +$('ad').textContent, kids = +$('ch').textContent, rooms = +$('rooms').textContent;
		var text = adults + (adults === 1 ? ' بالغ' : ' بالغين');
		if (kids) text += ' · ' + kids + (kids === 1 ? ' طفل' : ' أطفال');
		if (mode !== 'flight') text += ' · ' + rooms + (rooms === 1 ? ' غرفة' : ' غرف');
		byId('bk-trvt').textContent = text;
	}

	trv.addEventListener('click', function (e) {
		if (e.target.closest('.bk-pop') && !e.target.classList.contains('bk-done')) return;
		pop.classList.toggle('on');
	});
	trv.addEventListener('keydown', function (e) { if (e.key === 'Enter' && e.target === trv) pop.classList.toggle('on'); });
	document.addEventListener('click', function (e) { if (!e.target.closest('#f-trv')) pop.classList.remove('on'); });
	pop.addEventListener('click', function (e) {
		var btn = e.target.closest('button[data-s]');
		if (!btn) return;
		var el = $(btn.dataset.t);
		var v = Math.min(20, Math.max(+el.dataset.min, +el.textContent + +btn.dataset.s));
		el.textContent = v;
		travellerSummary();
	});

	/* City suggestions (Open-Meteo Geocoding, public, no key) */
	page.querySelectorAll('[data-ac]').forEach(function (input) {
		var list = document.createElement('ul');
		var timer;
		list.className = 'bk-sug';
		input.closest('.bk-f').appendChild(list);

		input.addEventListener('input', function () {
			clearTimeout(timer);
			delete input.dataset.cc;
			var q = input.value.trim();
			if (q.length < 2) { list.style.display = 'none'; return; }
			timer = setTimeout(function () {
				fetch(GEO_API + '?name=' + encodeURIComponent(q) + '&count=6&language=ar&format=json')
					.then(function (r) { return r.json(); })
					.then(function (json) {
						var results = json.results || [];
						list.innerHTML = results.map(function (x) {
							var place = [x.admin1, x.country].filter(Boolean).join('، ');
							var value = (x.name + (x.country ? '، ' + x.country : '')).replace(/"/g, '');
							return '<li data-v="' + value + '" data-name="' + x.name.replace(/"/g, '') + '" data-cc="' + (x.country_code || '') + '">' + icon('location_on') + '<div>' + x.name + '<small>' + place + '</small></div></li>';
						}).join('');
						list.style.display = results.length ? 'block' : 'none';
					})
					.catch(function () { list.style.display = 'none'; });
			}, 250);
		});

		list.addEventListener('mousedown', function (e) {
			var li = e.target.closest('li');
			if (!li) return;
			input.value = li.dataset.v;
			input.dataset.name = li.dataset.name;
			input.dataset.cc = li.dataset.cc;
			list.style.display = 'none';
		});
		input.addEventListener('blur', function () { setTimeout(function () { list.style.display = 'none'; }, 150); });
	});

	/* Send via WhatsApp: a polite Arabic quote request shaped by the chosen tab. */
	var MONTHS = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];

	function formatDate(v) {
		if (!v) return '';
		var p = v.split('-');
		return +p[2] + ' ' + MONTHS[+p[1] - 1] + ' ' + p[0];
	}

	function nights(a, b) {
		return Math.round((Date.parse(b) - Date.parse(a)) / 864e5);
	}

	function flag(cc) {
		return cc && cc.length === 2 ? ' ' + cc.toUpperCase().replace(/./g, function (c) { return String.fromCodePoint(127397 + c.charCodeAt(0)); }) : '';
	}

	// City as picked from the suggestions ("الرياض 🇸🇦"), or as typed.
	function place(id) {
		var input = $(id);
		return input.dataset.cc ? input.dataset.name + flag(input.dataset.cc) : input.value.trim();
	}

	function count(n, one, two, few, many) {
		return n === 1 ? one : n === 2 ? two : n <= 10 ? n + ' ' + few : n + ' ' + many;
	}

	function people() {
		var adults = +$('ad').textContent, kids = +$('ch').textContent;
		var text = count(adults + kids, 'شخص واحد', 'شخصان', 'أشخاص', 'شخصًا');
		if (kids) text += ' (' + count(adults, 'بالغ واحد', 'بالغان', 'بالغين', 'بالغًا') + ' و' + count(kids, 'طفل واحد', 'طفلان', 'أطفال', 'طفلًا') + ')';
		return text;
	}

	function bullets(list) {
		return list.filter(function (l) { return l[1]; }).map(function (l) { return '•\t' + l[0] + ': ' + l[1]; });
	}

	function buildMessage() {
		var subject = { flight: 'لتذكرة طيران', hotel: 'لحجز فندق', both: 'لباقة طيران وفندق' }[mode];
		var lines = ['السلام عليكم ورحمة الله وبركاته،', 'أرغب في الحصول على عرض سعر ' + subject + (BRAND ? ' من ' + BRAND : '') + '، وفق التفاصيل التالية:'];

		if (mode !== 'hotel') {
			lines.push('✈️ تفاصيل الرحلة');
			lines = lines.concat(bullets([
				['من', place('from')],
				['إلى', place('to')],
				['تاريخ السفر', formatDate($('dep').value)],
				['تاريخ العودة', oneWay ? '' : formatDate($('ret').value)],
				['نوع الرحلة', oneWay ? 'ذهاب فقط' : 'ذهاب وعودة'],
				['درجة السفر', $('cls').value],
				['عدد المسافرين', people()]
			]));
		}
		if (mode !== 'flight') {
			var inDate = mode === 'hotel' ? $('cin').value : $('dep').value;
			var outDate = mode === 'hotel' ? $('cout').value : (oneWay ? '' : $('ret').value);
			var n = inDate && outDate ? nights(inDate, outDate) : 0;
			var rooms = +$('rooms').textContent;
			lines.push('🏨 تفاصيل الإقامة');
			lines = lines.concat(bullets([
				['المدينة', place(mode === 'hotel' ? 'city' : 'to')],
				['تاريخ الدخول', formatDate(inDate)],
				['تاريخ الخروج', formatDate(outDate)],
				['عدد الليالي', n > 0 ? count(n, 'ليلة واحدة', 'ليلتان', 'ليالٍ', 'ليلة') : ''],
				['عدد الغرف', count(rooms, 'غرفة واحدة', 'غرفتان', 'غرف', 'غرفة')],
				['فئة الفندق', $('stars').value],
				['عدد النزلاء', mode === 'hotel' ? people() : '']
			]));
		}

		lines.push('👤 بيانات العميل');
		lines = lines.concat(bullets([['الاسم', $('name').value.trim()], ['رقم التواصل', $('tel').value.trim()], ['ملاحظات', $('note').value.trim()]]));

		var ask = {
			flight: 'يرجى تزويدي بأفضل الأسعار المتاحة، مع توضيح اسم شركة الطيران، وموعد الإقلاع والوصول، ووزن الأمتعة المسموح به، والسعر الإجمالي شامل الضرائب.',
			hotel: 'يرجى تزويدي بأفضل الخيارات المتاحة، مع توضيح اسم الفندق وموقعه، ونوع الغرفة، وهل الإفطار مشمول، والسعر الإجمالي شامل الضرائب.',
			both: 'يرجى تزويدي بأفضل العروض المتاحة، مع توضيح اسم شركة الطيران ومواعيد الرحلات ووزن الأمتعة، واسم الفندق ونوع الغرفة، والسعر الإجمالي شامل الضرائب.'
		}[mode];
		lines.push(ask, 'شكرًا لكم، وأتطلع إلى عرضكم.');
		return lines.join('\n');
	}

	byId('bk-send').addEventListener('click', function () {
		var required = mode === 'hotel' ? ['city', 'cin', 'cout'] : ['from', 'to', 'dep'].concat(oneWay ? [] : ['ret']);
		required = required.concat(['name', 'tel']);

		['from', 'to', 'dep', 'ret', 'city', 'cin', 'cout', 'name', 'tel'].forEach(function (id) { byId('f-' + id).classList.remove('err'); });
		var invalid = required.filter(function (id) { return !$(id).value.trim(); });
		invalid.forEach(function (id) { byId('f-' + id).classList.add('err'); });
		if (invalid.length) { $(invalid[0]).focus(); return; }

		window.open('https://wa.me/' + WA + '?text=' + encodeURIComponent(buildMessage()), '_blank', 'noopener');
	});

	setMode('flight');
})();
