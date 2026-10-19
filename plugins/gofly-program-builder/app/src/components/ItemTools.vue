<script setup>
// Move up / down, show / hide, delete for an item in an array.
const props = defineProps({ list: Array, index: Number });
function move(d) {
	const j = props.index + d;
	if (j < 0 || j >= props.list.length) return;
	const a = props.list;
	[a[j], a[props.index]] = [a[props.index], a[j]];
}
const toggle = () => (props.list[props.index].hide = !props.list[props.index].hide);
function remove() {
	if (confirm('حذف هذا العنصر؟')) props.list.splice(props.index, 1);
}
</script>

<template>
	<span class="tools">
		<button class="mini" type="button" title="أعلى" @click="move(-1)">▲</button>
		<button class="mini" type="button" title="أسفل" @click="move(1)">▼</button>
		<button class="mini" type="button" :title="list[index].hide ? 'إظهار' : 'إخفاء'" @click="toggle">{{ list[index].hide ? '🙈' : '👁' }}</button>
		<button class="mini" type="button" title="حذف" @click="remove">✕</button>
	</span>
</template>
