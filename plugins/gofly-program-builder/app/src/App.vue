<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import cfg from './api';
import { buildPdf } from './pdf';
import { COMPANY_FIELDS, COVER_FIELDS, META_FIELDS, STYLES, T } from './schema';
import { clearDraft, fromPackage, logDownload, openProgram, readDraft, restoreDraft, save, saveSettings, state, toast } from './store';
import Field from './components/Field.vue';
import LibraryPanel from './components/LibraryPanel.vue';
import Panel from './components/Panel.vue';
import Preview from './components/Preview.vue';
import SectionEditor from './components/SectionEditor.vue';
import StartPanel from './components/StartPanel.vue';
import SyncModal from './components/SyncModal.vue';

const view = ref('edit'); // mobile tab
const zoom = ref(1);
const preview = ref(null);
const newType = ref('text');
const draft = ref(null);
const exporting = ref('');

const style = computed(() => STYLES.find((x) => x[0] === state.P.tpl) || STYLES[0]);
const fileName = () => (state.P.cover.title || 'برنامج') + (state.P.meta.code ? ' - ' + state.P.meta.code : '') + '.pdf';

function fit() { zoom.value = innerWidth < 900 ? Math.min(1, (innerWidth - 28) / 794) : Math.min(1, (innerWidth - 560) / 794); }

function addSection() {
	const t = newType.value;
	state.P.secs.push(Object.assign({ t, title: T[t].n, mark: '✓' }, T[t].it ? { items: [{}] } : {}));
}

async function doSave(asNew) { try { await save(asNew); } catch (e) { toast(e.message); } }

async function makePdf() {
	if (state.dirty) await save();
	const v0 = view.value, z0 = zoom.value;
	view.value = 'prev';
	zoom.value = 1;
	scrollTo(0, 0);
	await nextTick();
	try { return await buildPdf(preview.value.el); } finally { view.value = v0; zoom.value = z0; }
}

async function download(share) {
	exporting.value = share ? 'share' : 'pdf';
	try {
		const { doc, pages } = await makePdf();
		const name = fileName();
		logDownload(pages);
		if (share) {
			const file = new File([doc.output('blob')], name, { type: 'application/pdf' });
			if (navigator.canShare && navigator.canShare({ files: [file] })) {
				try { await navigator.share({ files: [file] }); return; } catch (e) { if (e.name === 'AbortError') return; }
			}
		}
		doc.save(name);
	} catch (e) {
		toast('تعذر إنشاء PDF: ' + (e.message || e));
	} finally {
		exporting.value = '';
	}
}

async function publish() {
	if (!state.id || state.dirty) await doSave();
	if (state.id) state.syncOpen = true;
}

const leave = (e) => { if (state.dirty) { e.preventDefault(); e.returnValue = ''; } };

onMounted(async () => {
	fit();
	addEventListener('resize', fit);
	addEventListener('beforeunload', leave);
	try {
		if (cfg.open.program) await openProgram(cfg.open.program);
		else if (cfg.open.package) await fromPackage(cfg.open.package);
		else {
			const d = readDraft();
			if (d && d.P) draft.value = d;
		}
	} catch (e) { toast(e.message); }
});
onBeforeUnmount(() => { removeEventListener('resize', fit); removeEventListener('beforeunload', leave); });

function useDraft() { restoreDraft(draft.value); draft.value = null; toast('تمت استعادة المسودة غير المحفوظة'); }
function dropDraft() { clearDraft(); draft.value = null; }
</script>

<template>
	<div class="gfpb">
		<div class="bar">
			<img :src="state.CO.logo || cfg.logo" alt="">
			<b>أداة البرامج السياحية <small v-if="state.P.meta.code">· {{ state.P.meta.code }}</small><small v-if="state.dirty" class="dot"> · غير محفوظ</small></b>
			<button class="btn g" type="button" :disabled="!!state.busy" @click="doSave(false)">{{ state.busy === 'save' ? 'جارٍ الحفظ…' : 'حفظ البرنامج' }}</button>
			<button v-if="state.id" class="btn" type="button" @click="doSave(true)">حفظ كنسخة جديدة</button>
			<button v-if="cfg.caps.push" class="btn" type="button" @click="publish">{{ state.package ? 'تحديث الباقة على الموقع' : 'نشر على الموقع' }}</button>
			<button class="btn" type="button" :disabled="!!exporting" @click="download(false)">{{ exporting === 'pdf' ? 'جارٍ التحضير…' : 'تحميل PDF' }}</button>
			<button class="btn" type="button" :disabled="!!exporting" @click="download(true)">مشاركة</button>
			<a v-if="cfg.caps.view_log" class="btn" :href="cfg.logUrl">سجل النشاط</a>
		</div>

		<div v-if="draft" class="notice">
			لديك مسودة غير محفوظة ({{ draft.P.cover.title }}).
			<button class="mini" type="button" @click="useDraft">استعادة</button>
			<button class="mini" type="button" @click="dropDraft">تجاهل</button>
		</div>
		<div v-if="state.package" class="notice notice--link">
			🔗 مرتبط بباقة الموقع: <a :href="state.package.link" target="_blank">{{ state.package.title }}</a>
		</div>

		<div class="tabs">
			<button class="btn" type="button" :class="{ on: view === 'edit' }" @click="view = 'edit'">تحرير</button>
			<button class="btn" type="button" :class="{ on: view === 'prev' }" @click="view = 'prev'">معاينة</button>
		</div>

		<div class="app" :data-v="view">
			<div id="ed">
				<StartPanel />
				<LibraryPanel />

				<Panel title="بيانات البرنامج (داخلية للشركة - لا تظهر للعميل)" open>
					<Field v-for="f in META_FIELDS" :key="f[0]" :obj="state.P.meta" :k="f[0]" :label="f[1]" />
					<small>اترك الكود فارغًا ليُنشأ تلقائيًا عند الحفظ.</small>
				</Panel>

				<Panel title="قالب التصميم (يبقى الشعار والألوان كما هي)" open>
					<div class="styles">
						<button v-for="s in STYLES" :key="s[0]" type="button" :class="{ on: state.P.tpl === s[0] }" @click="state.P.tpl = s[0]">
							<span class="sw-thumb" :data-t="s[0]"></span>{{ s[1] }}
						</button>
					</div>
					<small>{{ style[2] }}</small>
				</Panel>

				<Panel title="بيانات الشركة الثابتة (تظهر أسفل كل صفحة)">
					<Field v-for="f in COMPANY_FIELDS" :key="f[0]" :obj="state.CO" :k="f[0]" :label="f[1]" :type="f[2]" :options="f[3]" :disabled="!cfg.caps.settings" />
					<button v-if="cfg.caps.settings" class="btn g" type="button" @click="saveSettings().catch((e) => toast(e.message))">حفظ الهوية للجميع</button>
					<small v-else>يعدّل مدير الموقع هذه البيانات لجميع المستخدمين.</small>
				</Panel>

				<Panel title="الغلاف" open>
					<Field v-for="f in COVER_FIELDS" :key="f[0]" :obj="state.P.cover" :k="f[0]" :label="f[1]" :type="f[2]" />
				</Panel>

				<SectionEditor v-for="(s, i) in state.P.secs" :key="i + s.t" :secs="state.P.secs" :index="i" />

				<div class="add">
					<select v-model="newType"><option v-for="(v, k) in T" :key="k" :value="k">{{ v.n }}</option></select>
					<button class="btn g" type="button" @click="addSection">إضافة قسم</button>
				</div>
			</div>

			<div class="prev" :style="{ '--z': zoom }">
				<Preview ref="preview" />
			</div>
		</div>

		<SyncModal v-if="state.syncOpen" />
		<div v-if="state.toast" class="toast">{{ state.toast }}</div>
	</div>
</template>
