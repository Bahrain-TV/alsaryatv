<template>
  <div class="family-form-container">
    <div class="family-form-content">
      <!-- existing code -->
    </div>
  </div>
</template>

<script>
  // existing code
</script>

<style scoped>
.family-form-container {
  overflow: hidden; /* Change from potential overflow: auto/scroll */
  position: relative;
  /* Keep any existing animation properties */
}

.family-form-content {
  max-height: 80vh; /* Adjust as needed */
  overflow-y: auto;
  /* Keep any existing animation properties */
}

.family-form-content .scrollable-element {
  overflow: visible;
}

/* Keep other existing styles */
</style>
