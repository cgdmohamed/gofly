<script setup>
defineProps({ title: String, open: Boolean });
</script>

<template>
	<details :open="open">
		<summary>{{ title }}</summary>
		<div class="bd"><slot /></div>
	</details>
</template>
