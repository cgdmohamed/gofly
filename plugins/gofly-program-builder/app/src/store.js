// App state and actions. One reactive object; components read it directly.

import { reactive, watch } from 'vue';
import cfg, { api } from './api';
import { DEF, normalize } from './schema';

const DRAFT_KEY = 'gfpb_draft_' + (cfg.user?.id || 0);

export const state = reactive({
	P: normalize(DEF()),
	CO: { ...cfg.settings },
	id: 0, // saved program id (0 = unsaved)
	package: null, // linked website package {id,title,link}
	codePrefix: '', // from a content template, e.g. RU-M5
	source: '', // 'template' | 'package' for the activity log
	sourceLabel: '',
	dirty: false,
	busy: '',
	toast: '',
	askSync: true, // ask to update the linked package after each save
	syncOpen: false,
});

let toastTimer;
export function toast(msg) {
	state.toast = msg;
	clearTimeout(toastTimer);
	toastTimer = setTimeout(() => (state.toast = ''), 4000);
}

let loading = false;
watch(() => state.P, () => {
	if (loading) return;
	state.dirty = true;
	try { localStorage.setItem(DRAFT_KEY, JSON.stringify({ id: state.id, P: state.P, t: Date.now() })); } catch (e) { /* storage full or disabled */ }
}, { deep: true });

function setDoc(doc, { id = 0, pkg = null } = {}) {
	loading = true;
	state.P = normalize(doc);
	state.id = id;
	state.package = pkg;
	state.dirty = false;
	setTimeout(() => (loading = false));
}

export function clearDraft() { try { localStorage.removeItem(DRAFT_KEY); } catch (e) { /* ignore */ } }
export function readDraft() { try { return JSON.parse(localStorage.getItem(DRAFT_KEY) || 'null'); } catch (e) { return null; } }
export function restoreDraft(draft) { setDoc(draft.P, { id: draft.id }); state.dirty = true; }

/* Programs ------------------------------------------------------------ */

export async function openProgram(id) {
	const p = await api('/programs/' + id);
	setDoc(p.data, { id: p.id, pkg: p.package });
	state.codePrefix = state.source = state.sourceLabel = '';
	clearDraft();
	toast('تم فتح البرنامج ' + (p.code || ''));
}

export function newProgram(doc = DEF(), opts = {}) {
	const keep = { exec: state.P.meta.exec, sales: state.P.meta.sales || cfg.user?.name || '' };
	const d = normalize(doc);
	d.meta = { ...d.meta, code: '', cname: '', cphone: '', ...keep };
	d.tpl = opts.keepStyle === false ? d.tpl : state.P.tpl || d.tpl;
	setDoc(d, { pkg: opts.package || null });
	state.codePrefix = opts.codePrefix || '';
	state.source = opts.source || '';
	state.sourceLabel = opts.sourceLabel || '';
	state.dirty = true;
}

export async function fromTemplate(tpl) {
	const t = await api('/templates/' + tpl.id);
	newProgram(t.data, { codePrefix: t.code, source: 'template', sourceLabel: t.title, keepStyle: false });
	toast('تم إنشاء برنامج من القالب — أضف الطيران والسعر وبيانات العميل ثم احفظ');
}

export async function fromPackage(id) {
	const r = await api('/packages/' + id + '/program');
	newProgram(r.data, { package: r.package, source: 'package', sourceLabel: r.package.title });
	toast('تم إنشاء برنامج من الباقة «' + r.package.title + '»');
}

export async function save(asNew = false) {
	state.busy = 'save';
	try {
		let p;
		if (state.id && !asNew) {
			p = await api('/programs/' + state.id, { method: 'PUT', body: { data: state.P } });
		} else {
			const data = JSON.parse(JSON.stringify(state.P));
			if (asNew) data.meta.code = '';
			p = await api('/programs', { method: 'POST', body: { data, package_id: state.package?.id || 0, code_prefix: state.codePrefix, source: state.source, source_label: state.sourceLabel } });
		}
		setDoc(p.data, { id: p.id, pkg: p.package });
		state.source = state.sourceLabel = '';
		clearDraft();
		toast('تم حفظ البرنامج: ' + p.code);
		if (cfg.caps.push && state.package && state.askSync) state.syncOpen = true;
		return p;
	} finally {
		state.busy = '';
	}
}

export const listPrograms = (search, scope) => api('/programs', { query: { search, scope } });
export const duplicate = async (id) => { const p = await api('/programs/' + id + '/duplicate', { method: 'POST' }); toast('تم نسخ البرنامج: ' + p.code); return p; };
export const removeProgram = async (id) => { await api('/programs/' + id, { method: 'DELETE' }); if (id === state.id) newProgram(); toast('تم حذف البرنامج'); };

/* Templates, packages, settings -------------------------------------- */

export const listTemplates = () => api('/templates');
export const listPackages = (search) => api('/packages', { query: { search } });
export const deleteTemplate = (id) => api('/templates/' + id, { method: 'DELETE' });
export async function saveAsTemplate(title) {
	await api('/templates', { method: 'POST', body: { title, data: state.P, code: state.codePrefix } });
	toast('تم حفظ القالب «' + title + '»');
}

export async function saveSettings() {
	state.CO = await api('/settings', { method: 'POST', body: state.CO });
	toast('تم حفظ بيانات الشركة لجميع المستخدمين');
}

export const packageDiff = (packageId) => api('/programs/' + state.id + '/package-diff', { method: 'POST', body: packageId !== undefined ? { package_id: packageId } : {} });
export async function pushPackage(fields, packageId) {
	const r = await api('/programs/' + state.id + '/push', { method: 'POST', body: { fields, ...(packageId !== undefined ? { package_id: packageId } : {}) } });
	state.package = r.package;
	toast(r.package.status === 'draft' ? 'تم إنشاء باقة جديدة كمسودة على الموقع' : 'تم تحديث الباقة على الموقع');
	return r;
}

export const logDownload = (pages) => state.id && api('/programs/' + state.id + '/download', { method: 'POST', body: { style: state.P.tpl, pages } }).catch(() => {});
