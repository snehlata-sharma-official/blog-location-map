document.addEventListener('DOMContentLoaded', function () {
  // Ensure the map container exists before initializing
  var mapContainer = document.getElementById('map');
  if (mapContainer) {
    var map = L.map('map').setView([0, 0], 2); // Default global view

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
    }).addTo(map);

    var posts = blog_map_map_data; // Blog posts with lat/lng data

    posts.forEach(function (post) {
      var marker = L.marker([post.lat, post.lng]).addTo(map);
      marker.bindPopup(
        `<b><a href="${post.link}">${post.title}</a></b>`
      );
    });
  } else {
    console.error('Map container not found.');
  }
});
