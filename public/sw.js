const CACHE_NAME = "siskul-v4";
const STATIC_ASSETS = ["/css/app-custom.css", "/offline.html"];
const EXCLUDE_FROM_CACHE = ["/login", "/logout", "/csrf-token", "/"];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS)),
  );
  self.skipWaiting();
});

// ← pindah ke sini, bukan di fetch
self.addEventListener("activate", (event) => {
  event.waitUntil(clients.claim());
});

self.addEventListener("fetch", (event) => {
  if (event.request.method !== "GET") return;

  const url = new URL(event.request.url);

  if (EXCLUDE_FROM_CACHE.includes(url.pathname)) {
    event.respondWith(
      fetch(event.request).catch(() => {
        // Kalau offline dan login page tidak bisa diakses, return response kosong
        return new Response("Tidak ada koneksi internet.", { status: 503 });
      }),
    );
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        const clone = response.clone();
        caches
          .open(CACHE_NAME)
          .then((cache) => cache.put(event.request, clone));
        return response;
      })
      .catch(() =>
        caches
          .match(event.request)
          .then((cached) => cached || caches.match("/offline.html")),
      ),
  );
});
