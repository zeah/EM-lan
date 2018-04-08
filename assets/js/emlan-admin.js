(() => {

	console.log(emlan.sort);
	meta = emlan.meta;
	let container = document.querySelector('.emlan-container');
	let info = new Map([
			['Lånebeløp', {name: 'belop', type: 'text'}],
			['Nedbetalingstid', {name: 'nedbetaling', type: 'text'}],
			['Aldersgrense', {name: 'alder', type: 'text'}],
			['Effektiv rente', {name: 'effrente', type: 'text'}],
			['Info 1', {name: 'info1', type: 'text'}],
			['Info 2', {name: 'info2', type: 'text'}],
			['Info 3', {name: 'info3', type: 'text'}],
			['Eksempel Effektiv rente', {name: 'ekseffrente', type: 'text'}],
			['Les Mer Lenke', {name: 'lesmer', type: 'text'}],
			['Få Tilbud lenke', {name: 'fatilbud', type: 'text'}]
		]);

	let input = (title_text, o) => {
		let container = document.createElement('div');
		container.classList.add('emlan-input-container');

		let title = document.createElement('div');
		title.classList.add('emlan-title');
		title.appendChild(document.createTextNode(title_text));
		container.appendChild(title);

		let input = document.createElement('input');
		input.classList.add('emlan-input');
		input.setAttribute('type', o.type);
		input.setAttribute('name', 'emlan['+o.name+']');
		if (meta[o.name]) input.setAttribute('value', meta[o.name]);

		container.appendChild(input);

		if (o.type == 'text') {
			let counter = document.createElement('span');
			counter.classList.add('emlan-counter');
			counter.appendChild(document.createTextNode(input.value.length));
			input.addEventListener('input', () => counter.innerHTML = input.value.length );
			container.appendChild(counter);
		}

		return container;
	}

	let order = input('Sortering', {name: 'sort', type: 'number'});

	for (let n of order.childNodes)
		if (n.tagName == 'INPUT') {
			n.setAttribute('name', 'emlan_sort');
			n.setAttribute('value', emlan.sort);
		}

	container.appendChild(order);

	for (let [i, v] of info)
		container.appendChild(input(i, v));

})();