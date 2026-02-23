document.getElementById("search-form").addEventListener("submit", function (e) {
  e.preventDefault();
  const searchInput = this.querySelector('input[name="search"]');
  const search = searchInput.value.trim();
  
  const url = new URL(window.location.href);
  const params = url.searchParams;

  // Update search param
  if (search) {
    params.set("search", search);
  } else {
    params.delete("search");
  }

  // Reset page to 1 on new search, but remove 'page' param if it is 1 (clean URL)
  params.delete("page");

  // Clean up other empty params
  const keysToDelete = [];
  params.forEach((value, key) => {
    if (value === "" || value === null || value === undefined) {
      keysToDelete.push(key);
    }
  });
  keysToDelete.forEach(key => params.delete(key));

  // Trigger SPA navigation via hidden link
  const link = document.createElement("a");
  link.href = url.toString();
  link.setAttribute("data-spa", "true");
  link.style.display = "none";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
});

