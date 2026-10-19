// A4 PDF export — port of the original builder's mk(): the cover is captured
// as page 1, then the body is captured once and sliced into pages that avoid
// cutting through day/hotel/flight cards, with the header and footer stamped
// on every page and hotel links kept clickable.

import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';

/**
 * @param {HTMLElement} paper The rendered .gfpb-paper element (zoom must be 1).
 * @returns {Promise<{doc: jsPDF, pages: number}>}
 */
export async function buildPdf(paper) {
	await document.fonts.ready;
	await Promise.all([...paper.querySelectorAll('img')].map((i) => (i.complete ? null : new Promise((r) => { i.onload = i.onerror = r; }))));

	const bg = getComputedStyle(paper).backgroundColor || '#fff';
	const W = paper.offsetWidth, f = W / 210;
	const bd = paper.querySelector('.body'), hd = bd.querySelector('.hd'), ft = bd.querySelector('.ft');
	const O = { scale: 2, useCORS: true, backgroundColor: bg };

	const cc = await html2canvas(paper.querySelector('.cover'), O);
	const hc = await html2canvas(hd, O);
	const fc = await html2canvas(ft, O);

	const pr = bd.getBoundingClientRect();
	const R = (e) => { const r = e.getBoundingClientRect(); return [r.top - pr.top, r.bottom - pr.top]; };
	const s = 1.5;
	const bc = await html2canvas(bd, {
		scale: s, useCORS: true, backgroundColor: bg, windowWidth: W + 40,
		onclone: (d) => ['.body>.hd', '.body>.ft'].forEach((q) => { const e = d.querySelector('.gfpb-paper ' + q); if (e) e.style.visibility = 'hidden'; }),
	});

	const hh = (210 * hc.height) / hc.width, fh = (210 * fc.height) / fc.width, H = (297 - hh - fh - 6) * f;
	// Blocks that should not be split across pages; headings stay with their first block.
	const blocks = [...bd.querySelectorAll('.dy,.hc,li,.pwrap,.fl,.fs,.sec>.tx')].map(R)
		.concat([...bd.querySelectorAll('.sec>h2')].map((e) => { const a = R(e); return [a[0], a[1] + 25 * f]; }));
	const last = [...bd.querySelectorAll('.sec')].pop(), y0 = R(hd)[1], total = last ? R(last)[1] + 4 * f : y0, pages = [];
	let y = y0;
	while (y < total - 2) {
		let L = y + H;
		if (L < total) {
			let m = L;
			blocks.forEach(([t, b]) => { if (t > y + 5 && t < L && b > L && b - t <= 90 * f) m = Math.min(m, t); });
			L = m;
		} else L = total;
		pages.push([y, L]);
		y = L;
	}

	const doc = new jsPDF({ unit: 'mm', format: 'a4' });
	doc.addImage(cc.toDataURL('image/jpeg', 0.92), 'JPEG', 0, 0, 210, 297);
	pages.forEach(([a, b]) => {
		doc.addPage();
		const rgb = (bg.match(/\d+/g) || []).map(Number);
		if (rgb.length >= 3 && rgb.slice(0, 3).join() !== '255,255,255') { doc.setFillColor(rgb[0], rgb[1], rgb[2]); doc.rect(0, 0, 210, 297, 'F'); }
		const c = document.createElement('canvas');
		c.width = bc.width;
		c.height = Math.max(1, Math.round((b - a) * s));
		c.getContext('2d').drawImage(bc, 0, Math.round(a * s), bc.width, c.height, 0, 0, bc.width, c.height);
		doc.addImage(c.toDataURL('image/jpeg', 0.92), 'JPEG', 0, hh + 3, 210, (b - a) / f);
		doc.addImage(hc.toDataURL('image/png'), 'PNG', 0, 0, 210, hh);
		doc.addImage(fc.toDataURL('image/png'), 'PNG', 0, 297 - fh, 210, fh);
		bd.querySelectorAll('a.lk').forEach((k) => {
			const r = k.getBoundingClientRect(), t = r.top - pr.top;
			if (t >= a && t < b) doc.link((r.left - pr.left) / f, hh + 3 + (t - a) / f, r.width / f, r.height / f, { url: k.href });
		});
	});
	return { doc, pages: pages.length + 1 };
}
