<script setup>
// One editable field bound to obj[k]. Types: text, ta, sel, img, chk, date, time, color.
// Textareas with several lines get per-line show/hide chips (stored in obj['off_' + k]).
import { computed } from 'vue';
import cfg from '../api';

const props = defineProps({ obj: Object, k: String, label: String, type: { type: String, default: 'text' }, options: Array, disabled: Boolean });

const lines = computed(() => (props.obj[props.k] || '').split('\n').filter((x) => x.trim()));
const offKey = computed(() => 'off_' + props.k);
const isOn = (l) => !(props.obj[offKey.value] || []).includes(l);
function toggle(l, on) {
	const s = new Set(props.obj[offKey.value] || []);
	on ? s.delete(l) : s.add(l);
	props.obj[offKey.value] = [...s];
}

const preview = (v) => (v && String(v).startsWith('tpl:') ? cfg.presetUrl + (cfg.presetImages[String(v).slice(4)] || '') : v);

function pickImage() {
	if (!window.wp?.media) return;
	const frame = window.wp.media({ title: props.label, library: { type: 'image' }, multiple: false, button: { text: 'اختيار' } });
	frame.on('select', () => {
		const a = frame.state().get('selection').first().toJSON();
		props.obj[props.k] = (a.sizes?.large || a.sizes?.full || a).url;
	});
	frame.open();
}
</script>

<template>
	<div>
		<label class="f" :class="{ 'f--chk': type === 'chk' }">
			<span>{{ label }}</span>
			<textarea v-if="type === 'ta'" v-model="obj[k]" rows="5" :disabled="disabled"></textarea>
			<select v-else-if="type === 'sel'" v-model="obj[k]" :disabled="disabled">
				<option v-for="o in options" :key="o" :value="o">{{ o }}</option>
			</select>
			<input v-else-if="type === 'chk'" v-model="obj[k]" type="checkbox" :disabled="disabled">
			<template v-else-if="type === 'img'">
				<span class="th">
					<img v-if="obj[k]" :src="preview(obj[k])" alt="">
					<button class="mini" type="button" :disabled="disabled" @click="pickImage">{{ obj[k] ? 'تغيير الصورة' : 'اختيار صورة' }}</button>
					<button v-if="obj[k]" class="mini" type="button" :disabled="disabled" @click="obj[k] = ''">حذف الصورة</button>
				</span>
			</template>
			<input v-else v-model="obj[k]" :type="['date', 'time', 'color'].includes(type) ? type : 'text'" :disabled="disabled">
		</label>
		<div v-if="type === 'ta' && lines.length > 1" class="chs">
			<small>إظهار/إخفاء الأسطر والفقرات:</small>
			<label v-for="l in lines" :key="l" class="chip">
				<input type="checkbox" :checked="isOn(l)" @change="toggle(l, $event.target.checked)">
				{{ l.length > 38 ? l.slice(0, 38) + '…' : l }}
			</label>
		</div>
	</div>
</template>
