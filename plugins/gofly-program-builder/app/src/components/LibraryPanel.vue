<script setup>
// Saved programs: search by code, customer name or phone; open, duplicate, delete.
import { onMounted, ref, watch } from 'vue';
import cfg from '../api';
import { duplicate, listPrograms, openProgram, removeProgram, state, toast } from '../store';
import Panel from './Panel.vue';

const q = ref('');
const scope = ref(cfg.caps.manage_all ? 'all' : 'mine');
const items = ref([]);
const loading = ref(false);
let timer;

async function load() {
	loading.value = true;
	try { items.value = (await listPrograms(q.value, scope.value)).items; } catch (e) { toast(e.message); } finally { loading.value = false; }
}
watch([q, scope], () => { clearTimeout(timer); timer = setTimeout(load, 300); });
watch(() => state.id, load);
onMounted(load);

const date = (v) => new Date(v).toLocaleDateString('ar-u-nu-latn');
async function open(p) {
	if (state.dirty && !confirm('لديك تغييرات غير محفوظة. فتح برنامج آخر؟')) return;
	try { await openProgram(p.id); } catch (e) { toast(e.message); }
}
async function copy(p) { try { await duplicate(p.id); load(); } catch (e) { toast(e.message); } }
async function del(p) {
	if (!confirm('حذف البرنامج ' + p.code + '؟')) return;
	try { await removeProgram(p.id); load(); } catch (e) { toast(e.message); }
}
</script>

<template>
	<Panel title="المكتبة: البرامج المحفوظة" open>
		<label class="f"><span>بحث</span><input v-model="q" type="search" placeholder="ابحث بالكود أو اسم العميل أو رقمه"></label>
		<div v-if="cfg.caps.manage_all" class="seg">
			<button type="button" :class="{ on: scope === 'all' }" @click="scope = 'all'">كل البرامج</button>
			<button type="button" :class="{ on: scope === 'mine' }" @click="scope = 'mine'">برامجي</button>
		</div>
		<small v-if="loading">جارٍ التحميل…</small>
		<small v-else-if="!items.length">{{ q ? 'لا توجد نتائج مطابقة' : 'لا توجد برامج محفوظة بعد' }}</small>
		<div v-for="p in items" :key="p.id" class="it" :class="{ cur: p.id === state.id }">
			<div><b>{{ p.code || '—' }}</b> · {{ p.customer || 'بدون اسم عميل' }}<template v-if="p.phone"> · <bdi>{{ p.phone }}</bdi></template></div>
			<small>{{ p.title }} — {{ date(p.updated) }}<template v-if="cfg.caps.manage_all"> — {{ p.author.name }}</template></small>
			<small v-if="p.package">🔗 {{ p.package.title }}</small>
			<div class="row">
				<button class="mini" type="button" @click="open(p)">فتح للتعديل</button>
				<span>
					<button class="mini" type="button" @click="copy(p)">نسخ</button>
					<button v-if="cfg.caps.manage_all" class="mini" type="button" @click="del(p)">حذف</button>
				</span>
			</div>
		</div>
	</Panel>
</template>
