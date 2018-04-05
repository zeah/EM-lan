(() => {

	let container = document.querySelector('.emlan-container');
	let info = ['belop'];

	let input = (title_text, name) => {
		let container = document.createElement('div');
		container.classList('emlan-input-container');

		let title = document.createElement('div');
		title.classList.add('emlan-title');
		title.appendChild(document.createTextNode(title_text));
		container.appendChild(title);

		let counter = document.createElement('span');
		counter.classList.add('emlan-counter');

		let input = document.createElement('input');
		input.classList.add('emlan-input');
		input.setAttribute('type', 'text');
		input.setAttribute('name', 'emlan['+name+']');
		input.addEventListener('input', () => counter.innerHTML = input.value.length );

		container.appendChild(input);

		container.appendChild(counter);

		return container;
	}


	for (let i of info)
		container.appendChild(input(i, i));

})();