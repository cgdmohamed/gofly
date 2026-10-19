<script setup>
import { computed } from 'vue';
import { T } from '../schema';
import Field from './Field.vue';
import ItemTools from './ItemTools.vue';
import Panel from './Panel.vue';

const props = defineProps({ secs: Array, index: Number });
// Computed so the editor follows the section when it is moved up or down.
const s = computed(() => {
	const sec = props.secs[props.index];
	if (T[sec.t].it && !Array.isArray(sec.items)) sec.items = [];
	return sec;
});
const d = computed(() => T[s.value.t]);
</script>

<template>
	<Panel :title="index + 1 + '. ' + (s.title || d.n) + (s.hide ? ' (مخفي)' : '')">
		<div class="row"><b>{{ d.n }}</b><ItemTools :list="secs" :index="index" /></div>
		<Field v-for="f in d.f" :key="f[0]" :obj="s" :k="f[0]" :label="f[1]" :type="f[2]" :options="f[3]" />
		<template v-if="d.it">
			<div v-for="(x, j) in s.items" :key="j" class="it" :class="{ off: x.hide }">
				<div class="row"><b>{{ d.itn }} {{ j + 1 }}</b><ItemTools :list="s.items" :index="j" /></div>
				<Field v-for="f in d.it" :key="f[0]" :obj="x" :k="f[0]" :label="f[1]" :type="f[2]" />
			</div>
			<button class="mini" type="button" @click="s.items.push({})">+ إضافة {{ d.itn }}</button>
		</template>
	</Panel>
</template>
