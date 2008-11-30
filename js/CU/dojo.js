dojo.provide('CU.dojo');
dojo.require('dijit.Tree');
dojo.addOnLoad(function(){
dojo.declare('CU.dojo.QueryStoreModel', dijit.tree.ForestStoreModel, {
		getChildren: function(parentItem, complete_cb, error_cb) {
			if(parentItem.root)
				return this.inherited(arguments);

			var name = this.store.getValue(parentItem, 'name');
			var package = this.store.getValue(parentItem, 'parent');
			if(!package)
				package = '';

			this.store.fetch({ query: { format: 'json', package: name, parent: package }, onComplete: complete_cb, onError: error_cb });	
		},

		mayHaveChildren: function(item) {
			if(item.root)
				return true;

			if(this.store.getValue(item, 'type') == 'package')
				return true;

			return false;
		}
	});

	dojo.declare('CU.dojo.ChkTree', dijit.Tree, {
		_clickTarget: null,

		_createTreeNode: function(args) {
			return new CU.dojo._ChkTreeNode(args);
		},

		getIconClass: function(item, opened) {
			if(item.root || this.model.store.getValue(item, 'type') == 'package')
				return this.inherited(arguments);

			return 'class';
		},

		getLabelClass: function(item, opened) {
			if(item.root || this.model.store.getValue(item, 'type') == 'package')
				return this.inherited(arguments);

			return 'class';
		},

		onNodeChecked: function(node) {

		},

		onNodeUnchecked: function(node) {

		},

		onClick: function(item, node) {
			if(item.root || this.model.store.getValue(item, 'type') == 'package')
			{
				this._onExpandoClick({ node: node });
			}
			else
			{
				if(!node.checkNode)
					return;

				if(this._clickTarget && this._clickTarget.nodeName != 'INPUT')
					node.checkNode.checked = !node.checkNode.checked;

				if(node.checkNode.checked)
				{
					this.onNodeChecked(node);
				}
				else
				{
					this.onNodeUnchecked(node);
				}
			}
		},

		_onClick: function(evt) {
			this._clickTarget = evt.target;
			//If the target was a checkbox, ignore focusing the widget
			if(this._clickTarget.nodeName=='INPUT')
			{
					var nodeWidget = dijit.getEnclosingWidget(this._clickTarget);	
					this.onClick(nodeWidget.item, nodeWidget);
					return;
			}
			return this.inherited(arguments);
		}
	});

	dojo.declare('CU.dojo._ChkTreeNode', dijit._TreeNode, {
		setLabelNode: function(label) {
			if(this.item.root || this.tree.model.store.getValue(this.item, 'type') != 'class')
				return this.inherited(arguments);

			var chk = dojo.doc.createElement('input');
			chk.type = 'checkbox';
			this.labelNode.innerHTML = '';
			dojo.place(chk, this.expandoNode, 'after');
			this.labelNode.appendChild(dojo.doc.createTextNode(label));
			this.checkNode = chk;
			this.expandoNodeText.parentNode.removeChild(this.expandoNodeText);
		}
	});
});
