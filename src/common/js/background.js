// Create neural network animation
function createNeuralNetwork() {
    const container = document.getElementById('neuralNetwork');
    const nodes = 20;

    for (let i = 0; i < nodes; i++) {
        const node = document.createElement('div');
        node.className = 'node';
        node.style.left = Math.random() * 100 + '%';
        node.style.top = Math.random() * 100 + '%';
        node.style.animationDelay = Math.random() * 2 + 's';
        container.appendChild(node);

        // Create connections
        if (i > 0 && Math.random() > 0.5) {
            const connection = document.createElement('div');
            connection.className = 'connection';
            connection.style.left = Math.random() * 100 + '%';
            connection.style.top = Math.random() * 100 + '%';
            connection.style.width = Math.random() * 200 + 50 + 'px';
            connection.style.animationDelay = Math.random() * 3 + 's';
            container.appendChild(connection);
        }
    }
}

// Create floating particles
function createParticles() {
    const container = document.getElementById('particles');
    const particleCount = 50;

    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 6 + 's';
        particle.style.animationDuration = (10 + Math.random() * 4) + 's';
        container.appendChild(particle);
    }
}

// Initialize background animations
function initBackgroundAnimations() {
    createNeuralNetwork();
    createParticles();
}

// Call when page loads
window.addEventListener('load', initBackgroundAnimations);