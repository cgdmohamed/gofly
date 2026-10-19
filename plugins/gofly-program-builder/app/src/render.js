// A4 page renderer: a faithful port of the original builder's cov/hdr/ftr/sec/pv.
// Returns HTML strings; every user value is escaped, image URLs included.

import { ORD } from './schema';

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

/** Visible lines of a textarea field (lines unticked in the editor are hidden). */
export const V = (o, k) => (o[k] || '').split('\n').filter((l) => !(o['off_' + k] || []).includes(l)).join('\n');

export const mixc = (h, w) => {
	const n = parseInt(String(h).slice(1), 16), m = (v) => Math.round(v + (255 - v) * w);
	return 'rgb(' + m(n >> 16) + ',' + m((n >> 8) & 255) + ',' + m(n & 255) + ')';
};

const LEG = (x) => ['from', 'to', 'd1', 't1', 'd2', 't2', 'bags', 'wt', 'cond'].some((k) => x[k]);
const emp = (s) => (s.t === 'flights' ? !(s.items || []).some((x) => !x.hide && LEG(x)) : s.t === 'price' ? !(s.amount || (s.na && s.pa) || (s.nc && s.pc)) : false);
const ico = (s) => (s.t === 'list' ? (s.mark === '✕' ? '✖' : '✔') : { days: '🗓', hotels: '🛏', flights: '✈', price: '💰' }[s.t] || '🛡');
const fd = (v) => { if (!v) return ''; const d = new Date(v + 'T00:00'); return isNaN(d) ? v : d.toLocaleDateString('ar-u-nu-latn', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); };
const ft = (v) => { if (!v) return ''; const d = new Date('1970-01-01T' + v); return isNaN(d) ? v : d.toLocaleTimeString('ar-u-nu-latn', { hour: 'numeric', minute: '2-digit' }); };
const aD = (n) => (n === 1 ? 'يوم واحد' : n === 2 ? 'يومان' : n <= 10 ? n + ' أيام' : n + ' يومًا');
const aN = (n) => (n === 1 ? 'ليلة واحدة' : n === 2 ? 'ليلتان' : n <= 10 ? n + ' ليالٍ' : n + ' ليلة');
const tsz = (t) => { const l = String(t || '').length; return l > 30 ? ' style="font-size:22pt"' : l > 22 ? ' style="font-size:26pt"' : l > 14 ? ' style="font-size:32pt"' : ''; };
const num = (v) => parseFloat(String(v || '').replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[,\s]/g, '')) || 0;
const fmt = (v) => v.toLocaleString('en-US', { maximumFractionDigits: 2 });

/** Number of nights: explicit, from dates, or visible days − 1. */
export function nights(P) {
	const c = P.cover;
	if (c.nights) return String(c.nights);
	if (c.start && c.end) { const n = Math.round((new Date(c.end) - new Date(c.start)) / 864e5); if (n > 0) return String(n); }
	const d = P.secs.reduce((a, s) => a + (s.t === 'days' && !s.hide ? (s.items || []).filter((x) => !x.hide).length : 0), 0);
	return d > 1 ? String(d - 1) : '';
}

/**
 * Render a document into the page HTML.
 *
 * @param {object} P   Program document.
 * @param {object} CO  Company settings.
 * @param {object} ctx { logo, presetUrl, presetImages }
 */
export function render(P, CO, ctx) {
	const IS = (v) => (v && String(v).startsWith('tpl:') ? (ctx.presetImages[String(v).slice(4)] ? ctx.presetUrl + ctx.presetImages[String(v).slice(4)] : '') : v || '');
	const src = (v) => esc(IS(v));
	const LOGO = CO.logo || ctx.logo;
	let GD = 0;
	const fdd = (i) => { const s = P.cover.start; if (!s) return ''; const d = new Date(s + 'T00:00'); if (isNaN(d)) return ''; d.setDate(d.getDate() + i); return d.toLocaleDateString('ar-u-nu-latn', { weekday: 'long', day: 'numeric', month: 'long' }); };
	const dur = () => { const c = P.cover; if (c.sub) return c.sub; const n = +nights(P); return n ? aD(n + 1) + ' – ' + aN(n) : ''; };

	const sw = (h) => { const y = (v) => (h * v).toFixed(1); return '<svg class="sw" viewBox="0 0 210 ' + h + '" preserveAspectRatio="none"><path d="M0 ' + y(0.72) + ' C60 ' + y(1) + ' 130 ' + y(0.6) + ' 178 0 L190 0 C140 ' + y(0.8) + ' 60 ' + y(1.05) + ' 0 ' + y(0.9) + 'Z" fill="' + esc(CO.c2) + '"/><path d="M0 ' + y(0.66) + ' C60 ' + y(0.94) + ' 130 ' + y(0.54) + ' 168 0 L180 0 C130 ' + y(0.74) + ' 60 ' + y(0.98) + ' 0 ' + y(0.8) + 'Z" fill="' + esc(CO.c1) + '"/></svg>'; };

	const ftr = (h, inl) => {
		const y = (v) => (h * v).toFixed(1);
		return '<div class="ft"' + (inl ? ' style="height:' + h + 'mm"' : '') + '><svg viewBox="0 0 210 ' + h + '" preserveAspectRatio="none"><path d="M0 ' + y(0.4) + ' C70 ' + y(0.05) + ' 140 ' + y(0.65) + ' 210 ' + y(0.15) + ' L210 ' + h + ' L0 ' + h + 'Z" fill="' + esc(CO.c2) + '"/><path d="M0 ' + y(0.55) + ' C70 ' + y(0.2) + ' 140 ' + y(0.8) + ' 210 ' + y(0.3) + ' L210 ' + h + ' L0 ' + h + 'Z" fill="' + esc(CO.c1) + '"/></svg>'
			+ '<div class="ftx"><div class="fr"><span>' + [CO.phone, CO.wa && 'واتساب: ' + CO.wa, CO.web].filter(Boolean).map((x) => '<bdi>' + esc(x) + '</bdi>').join(' • ') + '</span><span>' + esc(CO.ar) + (CO.cr ? ' — س.ت ' + esc(CO.cr) : '') + '</span></div>' + (CO.addr ? '<div class="fa">' + esc(CO.addr) + '</div>' : '') + '</div></div>';
	};

	const hdr = (h, noImg) => {
		const c = P.cover, m = P.meta || {};
		return '<div class="hd">' + (noImg ? '' : c.img ? '<img class="hi" src="' + src(c.img) + '">' : '<div class="hi hg0"></div>') + sw(h) + '<img class="lg" src="' + esc(LOGO) + '">' + (c.showno && m.code ? '<div class="pill"><small>رقم العرض</small><b><bdi>' + esc(m.code) + '</bdi></b></div>' : '') + '</div>';
	};

	const sec = (s) => {
		let b = '';
		if (s.t === 'text') b = '<p class="tx">' + esc(V(s, 'body')) + '</p>';
		else if (s.t === 'list') b = '<ul>' + V(s, 'body').split('\n').filter((x) => x.trim()).map((l) => '<li class="' + (s.mark === '✕' ? 'x' : '') + '"><b>' + esc(s.mark || '•') + '</b><span>' + esc(l) + '</span></li>').join('') + '</ul>';
		else if (s.t === 'days') {
			b = (s.items || []).filter((x) => !x.hide).map((x) => {
				const g = GD++, dt = fdd(g);
				return '<div class="dy"><span class="dc">' + (g + 1) + '</span><b class="dn">اليوم ' + (ORD[g] || g + 1) + (dt ? '<span class="dd">' + dt + '</span>' : '') + '</b><i class="di">' + (/انتقال|مطار|نقل|مغادرة|وصول/.test(x.kind || '') ? '🚌' : '☂️') + '</i><div class="dt">' + (x.img ? '<img src="' + src(x.img) + '">' : '') + '<b>' + esc(x.kind || 'جولة يومية') + (x.title ? ' — ' + esc(x.title) : '') + '</b><p class="tx">' + esc(V(x, 'body')) + '</p></div></div>';
			}).join('');
		} else if (s.t === 'flights') {
			const r = (l, v) => (v ? '<div><span>' + l + ':</span> <b>' + esc(v) + '</b></div>' : '');
			b = (s.items || []).filter((x) => !x.hide && LEG(x)).map((x, j) => '<div class="fl"><div class="fh">✈ ' + esc(x.title || 'رحلة ' + (j + 1)) + '</div><div class="fg">' + r('مطار الإقلاع', x.from) + r('مطار الوصول', x.to) + r('تاريخ المغادرة', fd(x.d1)) + r('وقت المغادرة', ft(x.t1)) + r('تاريخ الوصول', fd(x.d2)) + r('وقت الوصول', ft(x.t2)) + r('الحقائب لكل فرد', x.bags) + r('وزن الحقيبة', x.wt) + '</div>' + (x.cond ? '<p class="tx fc">' + esc(V(x, 'cond')) + '</p>' : '') + '</div>').join('')
				+ '<div class="fs">' + r('عدد الكبار', s.adults) + r('عدد الأطفال', s.kids) + (s.showp && s.price ? '<div>السعر: <b>' + esc(s.price) + ' ' + esc(s.cur) + '</b></div>' : '') + '</div>';
		} else if (s.t === 'hotels') {
			b = (s.items || []).filter((x) => !x.hide).map((x, j) => '<div class="hc"><div class="hp">' + (x.img ? '<img src="' + src(x.img) + '">' : '') + '<span class="hn">' + String(j + 1).padStart(2, '0') + '</span>' + (x.city ? '<span class="hl">' + esc(x.city) + '</span>' : '') + '</div><div class="hb2"><div class="hh"><b>' + esc(x.name) + '</b><span class="hs">' + '★'.repeat(Math.min(5, +x.stars || 0)) + '</span></div><div class="hr">' + [x.room && '🛏 ' + x.room, x.rooms && 'عدد الغرف: ' + x.rooms, x.nights && 'عدد الليالي: ' + x.nights].filter(Boolean).map(esc).join(' &nbsp;|&nbsp; ') + '</div><p class="tx">' + esc(V(x, 'body')) + '</p>' + (/^https?:\/\//i.test(x.url || '') ? '<a class="lk" href="' + esc(x.url) + '">🔗 مشاهدة الفندق</a>' : '') + '</div></div>').join('');
		} else if (s.t === 'price') {
			const cu = esc(s.cur || ''), sa = num(s.na) * num(s.pa), sc = num(s.nc) * num(s.pc), ds = num(s.disc), cl = sa + sc - ds, tn = s.amount ? num(s.amount) : cl, tt = s.amount ? esc(s.amount) : cl > 0 ? fmt(cl) : '', dp = num(s.dep);
			const r = (l, v) => (v ? '<tr><td>' + l + '</td><td>' + v + '</td></tr>' : '');
			b = '<div class="pwrap"><table class="pt">' + (num(s.na) && num(s.pa) ? r('البالغون (' + esc(s.na) + ' × ' + fmt(num(s.pa)) + ')', fmt(sa) + ' ' + cu) : '') + (num(s.nc) && num(s.pc) ? r('الأطفال (' + esc(s.nc) + ' × ' + fmt(num(s.pc)) + ')', fmt(sc) + ' ' + cu) : '') + (ds ? r('الخصم', '− ' + fmt(ds) + ' ' + cu) : '') + '</table>'
				+ (tt ? '<div class="pr">' + (s.old ? '<s>' + esc(s.old) + '</s>' : '') + '<strong>' + tt + '</strong><span>' + cu + '</span></div>' : '')
				+ (dp ? '<table class="pt">' + r('العربون المطلوب', fmt(dp) + ' ' + cu) + (tn > dp ? r('المتبقي', fmt(tn - dp) + ' ' + cu) : '') + '</table>' : '')
				+ (s.valid ? '<p class="vl">⏳ هذا العرض ساري حتى ' + esc(fd(s.valid)) + '</p>' : '') + (s.body ? '<p class="tx">' + esc(V(s, 'body')) + '</p>' : '') + '</div>'
				+ (s.showbank && CO.bank ? '<h3 class="bh">طرق الدفع والتحويل</h3><p class="tx bk">' + esc(CO.bank) + '</p>' : '');
		}
		return '<section class="sec"><h2><i class="ic ' + (s.t === 'list' && s.mark !== '✕' ? 'ok' : '') + '">' + ico(s) + '</i>' + esc(s.title) + '</h2>' + b + '</section>';
	};

	const cov = () => {
		const c = P.cover, m = P.meta || {}, bd = (c.badge || '').split(' ');
		const sv = V(c, 'svc').split('\n').filter((x) => x.trim()).slice(0, 6).map((l) => { const a = l.split('|'); return '<div class="sv">' + (a.length > 1 ? '<i>' + esc(a[0]) + '</i>' : '') + '<span>' + esc(a.length > 1 ? a.slice(1).join('|') : l) + '</span></div>'; }).join('');
		const card = c.showcard ? '<div class="ic2"><div class="ich">معلومات الحجز</div>' + [['اسم العميل', m.cname], ['عدد الليالي', nights(P)], ['عدد الأشخاص', c.persons]].filter((x) => x[1]).map((x) => '<div class="ir"><span>' + x[0] + ' :</span><b>' + esc(x[1]) + '</b></div>').join('') + '</div>' : '';
		return '<section class="cover"><div class="cph">' + (c.img ? '<img src="' + src(c.img) + '">' : '') + '</div><div class="ov"></div>' + hdr(38, 1)
			+ '<div class="ctx"><div class="ct"' + tsz(c.title) + '>' + esc(c.title) + '</div><div class="ce">' + esc(c.en) + '</div><div class="t1">' + esc(c.t1) + '</div><div class="t2">' + esc(c.t2) + '</div>' + (dur() ? '<div class="t3">' + esc(dur()) + '</div>' : '') + '</div>'
			+ card + (c.badge && c.showbdg ? '<div class="bdg"><b>' + esc(bd[0]) + '</b><span>' + esc(bd.slice(1).join(' ')) + '</span></div>' : '')
			+ (c.showsvc ? '<div class="svh"><span>خدماتنا المتوفرة</span></div><div class="svg">' + sv + '</div>' : '') + ftr(30) + '</section>';
	};

	return cov() + '<div class="body">' + hdr(34) + P.secs.filter((s) => !s.hide && !emp(s)).map(sec).join('') + ftr(CO.addr ? 21 : 16, 1) + '</div>';
}

/** CSS custom properties for the page (brand colours and their tints). */
export const paperVars = (CO) => ({ '--navy': CO.c1, '--gold': CO.c2, '--gold2': mixc(CO.c2, 0.35), '--tint': mixc(CO.c1, 0.93), fontFamily: (CO.font || 'Cairo') + ',Cairo,Tahoma,Arial,sans-serif' });
