document.addEventListener('DOMContentLoaded', function() {
    let selectedGoodId = null;

    function selectItem(event, list) {
        const items = document.querySelectorAll('.selectable');
        items.forEach(item => item.classList.remove('selected'));
        event.currentTarget.classList.add('selected');
        selectedGoodId = event.currentTarget.dataset.id;
        document.querySelector('#buy-form input[name="good_id"]').value = selectedGoodId;
        document.querySelector('#sell-form input[name="good_id"]').value = selectedGoodId;

        // Highlight the corresponding item in the other list
        if (list === 'available') {
            highlightInventoryItem(selectedGoodId);
        } else if (list === 'inventory') {
            highlightAvailableItem(selectedGoodId);
        }
    }

    function highlightAvailableItem(goodId) {
        const availableItems = document.querySelectorAll('#available-goods .selectable');
        availableItems.forEach(item => {
            if (item.dataset.id == goodId) {
                item.classList.add('selected');
            }
        });
    }

    function highlightInventoryItem(goodId) {
        const inventoryItems = document.querySelectorAll('#inventory-list .selectable');
        inventoryItems.forEach(item => {
            if (item.dataset.id == goodId) {
                item.classList.add('selected');
            }
        });
    }

    function travel() {
        const locationId = document.getElementById('location-select').value;
        fetch('travel.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `location_id=${locationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('current-location').textContent = data.current_location;
                updatePrices(data.goods);
                updateUserInfo(data.user);
            } else {
                showError(data.message);
            }
        });
    }

    function updatePrices(goods) {
        const availableGoodsList = document.getElementById('available-goods');
        availableGoodsList.innerHTML = '';
        goods.forEach(good => {
            const li = document.createElement('li');
            li.className = 'selectable';
            li.dataset.id = good.id;
            li.onclick = (event) => selectItem(event, 'available');
            li.textContent = `${good.name} - $${good.price}`;
            availableGoodsList.appendChild(li);
        });
    }

    function setQuantityAndBuy(quantity) {
        document.querySelector('#buy-form input[name="quantity"]').value = quantity;
        buy();
    }

    function setQuantityAndSell(quantity) {
        document.querySelector('#sell-form input[name="quantity"]').value = quantity;
        sell();
    }

    function buy() {
        const formData = new FormData(document.getElementById('buy-form'));
        fetch('buy.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Buy response:', data); // Log response for debugging
            if (data.status === 'error') {
                showError(data.message);
            } else {
                updateUserInfo(data.user);
                updateInventory(data.inventory);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function sell() {
        const formData = new FormData(document.getElementById('sell-form'));
        fetch('sell.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Sell response:', data); // Log response for debugging
            if (data.status === 'error') {
                showError(data.message);
            } else {
                updateUserInfo(data.user);
                updateInventory(data.inventory);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function showError(message) {
        const errorMessage = document.getElementById('error-message');
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        setTimeout(() => {
            errorMessage.style.display = 'none';
        }, 3000);
    }

    function updateUserInfo(user) {
        document.getElementById('cash').textContent = parseFloat(user.cash).toFixed(2);
        document.getElementById('bank').textContent = parseFloat(user.bank).toFixed(2);
        document.getElementById('debt').textContent = parseFloat(user.debt).toFixed(2);
    }

    function updateInventory(inventory) {
        const inventoryList = document.getElementById('inventory-list');
        inventoryList.innerHTML = '';
        inventory.forEach(item => {
            const li = document.createElement('li');
            li.className = 'selectable';
            li.dataset.id = item.id;
            li.onclick = (event) => selectItem(event, 'inventory');
            li.textContent = `${item.name} - ${item.quantity} @ $${parseFloat(item.average_price).toFixed(2)}`;
            inventoryList.appendChild(li);
        });
        updateTrenchcoatUsage(inventory);
    }

    function updateTrenchcoatUsage(inventory) {
        let totalQuantity = 0;
        inventory.forEach(item => {
            totalQuantity += parseInt(item.quantity);
        });
        document.getElementById('trenchcoat-usage').textContent = totalQuantity;
    }

    // Attach event listeners
    document.querySelectorAll('.selectable').forEach(item => {
        item.addEventListener('click', event => {
            selectItem(event, 'available');
        });
    });

    document.getElementById('location-select').addEventListener('change', travel);
});