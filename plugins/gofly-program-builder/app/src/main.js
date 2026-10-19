import { createApp } from 'vue';
import App from './App.vue';
import './styles/app.css';
import './styles/paper.css';

const el = document.getElementById('gfpb-app');
if (el) createApp(App).mount(el);
