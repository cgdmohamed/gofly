<script setup>
// "Update the website package?" — field-by-field diff with checkboxes.
import { onMounted, ref } from 'vue';
import cfg from '../api';
import { listPackages, packageDiff, pushPackage, state, toast } from '../store';

const rows = ref([]);
const pick = ref({});
const target = ref(state.package ? state.package.id : 0);
const packages = ref([]);
const loading = ref(true);
const sending = ref(false);

async function load() {
	loading.value = true;
	try {
		const r = await packageDiff(target.value);
		rows.value = r.rows;
		pick.value = Object.fromEntries(r.rows.map((x) => [x.field, x.changed && x.field !== 'subtitle']));
	} catch (e) { toast(e.message); } finally { loading.value = false; }
}
onMounted(async () => {
	if (!state.package) packages.value = await listPackages('').catch(() => []);
	load();
});

const close = () => (state.syncOpen = false);
async function apply() {
	const fields = Object.keys(pick.value).filter((k) => pick.value[k]);
	if (!fields.length) return close();
	sending.value = true;
	try { await pushPackage(fields, target.value); close(); } catch (e) { toast(e.message); } finally { sending.value = false; }
}
</script>

<template>
	<div class="modal-wrap" @click.self="close">
		<div class="modal" role="dialog" aria-modal="true">
			<h2>{{ state.package ? 'تحديث الباقة على الموقع؟' : 'نشر البرنامج على الموقع' }}</h2>
			<p v-if="state.package">هذا البرنامج مرتبط بالباقة «<a :href="state.package.link" target="_blank">{{ state.package.title }}</a>». اختر ما تريد تحديثه في صفحة الباقة. بيانات العميل والطيران لا تُنشر أبدًا.</p>
			<template v-else>
				<p>اختر باقة لتحديثها، أو أنشئ باقة جديدة كمسودة.</p>
				<select v-model="target" @change="load"><option :value="0">+ باقة جديدة (مسودة)</option><option v-for="x in packages" :key="x.id" :value="x.id">{{ x.title }}</option></select>
			</template>
			<p v-if="loading">جارٍ المقارنة…</p>
			<table v-else class="diff">
				<thead><tr><th></th><th>الحقل</th><th>على الموقع الآن</th><th>من البرنامج</th></tr></thead>
				<tbody>
					<tr v-for="r in rows" :key="r.field" :class="{ same: !r.changed }">
						<td><input v-model="pick[r.field]" type="checkbox"></td>
						<td>{{ r.label }}<small v-if="!r.changed"> (بدون تغيير)</small></td>
						<td><img v-if="r.field === 'thumbnail' && r.current" :src="r.current" alt=""><span v-else>{{ r.current || '—' }}</span></td>
						<td><img v-if="r.field === 'thumbnail' && r.next" :src="r.next" alt=""><span v-else>{{ r.next || '—' }}</span></td>
					</tr>
				</tbody>
			</table>
			<label v-if="state.package" class="chip"><input v-model="state.askSync" type="checkbox"> اسألني بعد كل حفظ</label>
			<div class="add end">
				<button class="mini" type="button" @click="close">لا، شكرًا</button>
				<button class="btn g" type="button" :disabled="loading || sending" @click="apply">{{ sending ? 'جارٍ التحديث…' : state.package || target ? 'تحديث الباقة' : 'إنشاء الباقة' }}</button>
			</div>
			<small v-if="cfg.packagesUrl"><a :href="cfg.packagesUrl" target="_blank">عرض كل الباقات</a></small>
		</div>
	</div>
</template>
