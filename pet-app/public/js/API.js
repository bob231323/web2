let pets = ["cat", "dog", "bird"];
let index = 0;

async function loadPet() {
    try {
        let pet = pets[index % pets.length];

        const response = await fetch("/api/pet?pet=" + pet);
        const data = await response.json();

        document.getElementById("fact").innerText = data.fact;
        document.getElementById("image").src = data.image;
        document.getElementById("pet").innerText = pet.toUpperCase();

        index++;

    } catch (error) {
        document.getElementById("fact").innerText =
            "Failed to load data";
    }
}

loadPet();
setInterval(loadPet, 3000);
