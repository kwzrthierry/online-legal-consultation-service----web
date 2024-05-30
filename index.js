// Dummy data for testimonials
const testimonials = [
    {
        name: "John Doe",
        comment: "I received excellent legal advice from the team at Legal Consulting Service. They helped me navigate through a complex legal issue with ease.",
        avatar: "img/4.jpg"
    },
    {
        name: "Jane Smith",
        comment: "The lawyers here are highly professional and knowledgeable. I highly recommend their services to anyone in need of legal assistance.",
        avatar: "img/4.jpg"
    },
    {
        name: "David Johnson",
        comment: "I'm grateful for the prompt and effective legal advice I received from Legal Consulting Service. They truly care about their clients' needs.",
        avatar: "img/4.jpg"
    }
];

// Display testimonials
const testimonialContainer = document.querySelector('.testimonials .container');
testimonials.forEach(testimonial => {
    const testimonialCard = document.createElement('div');
    testimonialCard.classList.add('testimonial-card');
    testimonialCard.innerHTML = `
        <img src="${testimonial.avatar}" alt="${testimonial.name}" class="avatar">
        <h2>${testimonial.name}</h2>
        <p>${testimonial.comment}</p>
        
    `;
    testimonialContainer.appendChild(testimonialCard);
});