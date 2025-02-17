let selectedCategories = [];

function toggleCategory(id, title) {
    const index = selectedCategories.findIndex(cat => cat.id === id);
    const element = document.querySelector(`.category-item[data-id="${id}"]`);
    
    if (index === -1) {
        // Add with animation
        selectedCategories.push({ id, title });
        element?.classList.add('selected');
        playSelectAnimation(element);
    } else {
        // Remove with animation
        selectedCategories.splice(index, 1);
        element?.classList.remove('selected');
        playUnselectAnimation(element);
    }
    
    updateSelectedCategories();
}

function playSelectAnimation(element) {
    element?.style.transform = 'scale(1.1)';
    setTimeout(() => {
        element.style.transform = 'translateY(-2px)';
    }, 200);
}

function playUnselectAnimation(element) {
    element?.style.transform = 'scale(0.9)';
    setTimeout(() => {
        element.style.transform = 'none';
    }, 200);
}

function updateSelectedCategories() {
    const container = document.getElementById('selected-categories');
    const input = document.getElementById('categories-input');
    
    if (!container || !input) return;
    
    container.innerHTML = selectedCategories.map(cat => `
        <span class="category-tag">
            ${cat.title}
            <span onclick="removeCategory(event, ${cat.id}, '${cat.title}')">&times;</span>
        </span>
    `).join('');
    
    input.value = selectedCategories.map(cat => cat.id).join(',');
}

function removeCategory(event, id, title) {
    event.stopPropagation();
    toggleCategory(id, title);
}

async function addNewCategory() {
    const input = document.getElementById('new-category');
    const title = input.value.trim();
    
    if (!title) return;
    
    try {
        const response = await fetch(`${ROOT_URL}admin/add-category-ajax.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ title })
        });
        
        const data = await response.json();
        if (data.success) {
            const categoriesContainer = document.querySelector('.categories-selector');
            const newCategory = document.createElement('div');
            newCategory.className = 'category-item';
            newCategory.setAttribute('data-id', data.id);
            newCategory.textContent = title;
            newCategory.onclick = () => toggleCategory(data.id, title);
            categoriesContainer.appendChild(newCategory);
            input.value = '';
            
            // Automatically select the new category
            toggleCategory(data.id, title);
        } else {
            alert(data.message || 'Failed to add category');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to add category. Please try again.');
    }
}
