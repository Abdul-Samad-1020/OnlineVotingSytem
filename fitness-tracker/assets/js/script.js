// Simple form validation and enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Confirm before delete
    const deleteLinks = document.querySelectorAll('a[onclick*="confirm"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this?')) {
                e.preventDefault();
            }
        });
    });

    // Calculate sleep duration
    const sleepStart = document.getElementById('start_time');
    const sleepEnd = document.getElementById('end_time');
    const sleepDuration = document.getElementById('duration');
    
    if (sleepStart && sleepEnd && sleepDuration) {
        function calculateDuration() {
            if (sleepStart.value && sleepEnd.value) {
                const start = new Date(sleepStart.value);
                const end = new Date(sleepEnd.value);
                const duration = (end - start) / (1000 * 60 * 60); // in hours
                sleepDuration.value = duration.toFixed(2);
            }
        }
        
        sleepStart.addEventListener('change', calculateDuration);
        sleepEnd.addEventListener('change', calculateDuration);
    }
});