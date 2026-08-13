<footer>
    <p>&copy; <?php echo date("Y"); ?> FitReserve App. All rights reserved.</p>
</footer>

<script>
document.querySelectorAll('[data-auto-dismiss]').forEach(function(message) {
    const timeout = Number(message.dataset.autoDismiss);
    window.setTimeout(function() {
        message.classList.add('message-fading');
        window.setTimeout(function() {
            message.remove();
        }, 1000);
    }, timeout);
});
</script>

</body>
</html>
