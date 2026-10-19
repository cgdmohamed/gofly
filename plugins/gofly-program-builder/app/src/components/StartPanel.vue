<script setup>
// Start a program: blank, from a content template, or from a website package.
import { onMounted, ref, watch } from 'vue';
import cfg from '../api';
import { deleteTemplate, fromPackage, fromTemplate, listPackages, listTemplates, newProgram, saveAsTemplate, state, toast } from '../store';
import Panel from './Panel.vue';

const templates = ref([]);
const tplId = ref('');
const packages = ref([]);
const pkgId = ref('');
const pkgQ = ref('');

const confirmLeave = () => !state.dirty || confirm('لديك تغييرات غير محفوظة في البرنامج الحالي. المتابعة؟');

async function loadTemplates() {
	templates.value = await listTemplates();
	if (!tplId.value && templates.value[0]) tplId.value = templates.value[0].id;
}
async function loadPackages() { if (cfg.hasPackages) packages.value = await listPackages(pkgQ.value); }
let t;
watch(pkgQ, () => { clearTimeout(t); t = setTimeout(loadPackages, 300); });
onMounted(() => { loadTemplates().catch((e) => toast(e.message)); loadPackages().catch(() => {}); });

async function useTemplate() {
	const tpl = templates.value.find((x) => x.id === +tplId.value);
	if (tpl && confirmLeave()) await fromTemplate(tpl).catch((e) => toast(e.message));
}
async function usePackage() { if (pkgId.value && confirmLeave()) await fromPackage(pkgId.value).catch((e) => toast(e.message)); }
function blank() { if (confirmLeave()) newProgram(); }

async function saveTpl() {
	const title = prompt('اسم القالب', state.P.cover.title || '');
	if (!title) return;
	try { await saveAsTemplate(title); await loadTemplates(); } catch (e) { toast(e.message); }
}
async function delTpl() {
	const tpl = templates.value.find((x) => x.id === +tplId.value);
	if (!tpl || !confirm('حذف القالب «' + tpl.title + '»؟')) return;
	try { await deleteTemplate(tpl.id); tplId.value = ''; await loadTemplates(); toast('تم حذف القالب'); } catch (e) { toast(e.message); }
}
</script>

<template>
	<Panel title="ابدأ برنامجًا جديدًا" open>
		<label class="f"><span>قالب محتوى جاهز</span>
			<select v-model="tplId"><option v-for="x in templates" :key="x.id" :value="x.id">{{ x.code ? x.code + ' · ' : '' }}{{ x.title }}</option></select>
		</label>
		<div class="add">
			<button class="btn g" type="button" :disabled="!tplId" @click="useTemplate">إنشاء من القالب</button>
			<button v-if="cfg.caps.templates" class="mini" type="button" :disabled="!tplId" @click="delTpl">حذف القالب</button>
		</div>
		<small>يُنشأ برنامج بكود جديد؛ تضيف الطيران والسعر وبيانات العميل فقط. الفنادق: فئة واحدة ظاهرة والباقي مخفي (بالأيقونة 👁).</small>

		<template v-if="cfg.hasPackages">
			<hr>
			<label class="f"><span>من باقة على الموقع</span><input v-model="pkgQ" type="search" placeholder="ابحث باسم الباقة"></label>
			<select v-model="pkgId" size="4" class="list"><option v-for="x in packages" :key="x.id" :value="x.id">{{ x.title }}{{ x.status !== 'publish' ? ' (' + x.status + ')' : '' }}</option></select>
			<button class="btn g" type="button" :disabled="!pkgId" @click="usePackage">إنشاء من الباقة</button>
		</template>

		<hr>
		<div class="add">
			<button class="mini" type="button" @click="blank">برنامج فارغ</button>
			<button v-if="cfg.caps.templates" class="mini" type="button" @click="saveTpl">حفظ البرنامج الحالي كقالب</button>
		</div>
	</Panel>
</template>
