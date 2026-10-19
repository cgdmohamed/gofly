<script setup>
// Live A4 page. Exposes the element so App can export it to PDF.
import { computed, ref } from 'vue';
import cfg from '../api';
import { paperVars, render } from '../render';
import { state } from '../store';

const el = ref(null);
const html = computed(() => render(state.P, state.CO, cfg));
const vars = computed(() => paperVars(state.CO));
defineExpose({ el });
</script>

<template>
	<div ref="el" class="gfpb-paper" :data-t="state.P.tpl || 'wave'" :style="vars" v-html="html"></div>
</template>
