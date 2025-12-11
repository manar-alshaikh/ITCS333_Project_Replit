document.addEventListener("DOMContentLoaded", () => {
    const goBackBtn = document.getElementById('go-back-btn');
    if (!goBackBtn) return;
    goBackBtn.addEventListener('click', () => {
        if (document.referrer) {
            window.location.href = document.referrer;
        } else {
            history.back();
        }
    });
});
