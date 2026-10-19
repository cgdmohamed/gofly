// Thin REST client for the gofly-pb/v1 namespace (nonce from wp_localize_script).

const cfg = window.GFPB || {};

export async function api(path, { method = 'GET', body, query } = {}) {
	const url = new URL(cfg.root + path, location.origin);
	Object.entries(query || {}).forEach(([k, v]) => v !== undefined && v !== '' && url.searchParams.set(k, v));
	const res = await fetch(url, {
		method,
		credentials: 'same-origin',
		headers: { 'X-WP-Nonce': cfg.nonce, ...(body ? { 'Content-Type': 'application/json' } : {}) },
		body: body ? JSON.stringify(body) : undefined,
	});
	const data = await res.json().catch(() => ({}));
	if (!res.ok) throw new Error(data.message || 'تعذر الاتصال بالخادم (' + res.status + ')');
	return data;
}

export default cfg;
