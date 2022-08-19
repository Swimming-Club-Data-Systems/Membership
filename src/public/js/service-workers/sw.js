// Names of the two caches used in this version of the service worker.
// Change to v2, etc. when you update any of the local resources, which will
// in turn trigger the install event again.
const PRECACHE = 'precache-v16';
const RUNTIME = 'runtime-v16';

// A list of local resources we always want to be cached.
const PRECACHE_URLS = [
  'manifest.webmanifest',
  'public/css/colour.css',
  'public/css/font-awesome/css/font-awesome.min.css',
  'public/css/font-awesome/fonts/fontawesome-webfont.woff2',
  'public/img/corporate/scds.png',
  'public/js/Cookies.js',
  'public/js/NeedsValidation.js',
  'public/js/tinymce/tinymce.min.js',
  'public/js/notify/TinyMCE.js',
  'public/js/notify/FileUpload.js',
  'pwa/offline',
];

// The install handler takes care of precaching the resources we always need.
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(PRECACHE).then(function(cache) {
      // console.log('Opened cache');
      return cache.addAll(PRECACHE_URLS);
    }).catch(error => {
    })
  );
});

// The activate handler takes care of cleaning up old caches.
self.addEventListener('activate', event => {
  const currentCaches = [PRECACHE, RUNTIME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return cacheNames.filter(cacheName => !currentCaches.includes(cacheName));
    }).then(cachesToDelete => {
      return Promise.all(cachesToDelete.map(cacheToDelete => {
        return caches.delete(cacheToDelete);
      }));
    }).then(() => self.clients.claim()).catch((error) => {
      console.log(error);
    })
  );
});

// The fetch handler serves responses for same-origin resources from a cache.
// If no response is found, it populates the runtime cache with the response
// from the network before returning it to the page.
self.addEventListener('fetch', event => {
  // console.log(event);
  // Skip cross-origin requests, like those for Google Analytics.
  let swRoot = self.registration.scope;
  if (event.request.url.startsWith(swRoot + 'public') && event.request.method == 'GET') {
    event.respondWith(
      caches.match(event.request).then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }

        return caches.open(RUNTIME).then(cache => {
          return fetch(event.request).then(response => {
            // Put a copy of the response in the runtime cache.
            return cache.put(event.request, response.clone()).then(() => {
              return response;
            });
          });
        });
      })
    );
  } else if (event.request.url.startsWith(swRoot) && event.request.method == 'GET') {
    event.respondWith(
      fetch(event.request).then(response => {
        // Don't cache - will start caching in future
        return response;
      }).catch(err => {
        return caches.match('pwa/offline');
      })
    );
  }
});
