
/* * * * Main Builder Class (Profile Fields Manager) * * * */

function BxDolPFM( aAreas ) {
	this.config = {
		areas: 0,
		parserUrl: '',
		inactiveColumns: 4,
		getAreaElem : function(id) { //function to get the area element. made to customizability
			var $area = $( '#m' + id + ' > div.build_container' );
            return $area.length ? $area.get(0) : false;
		}
	};
	
	this.areas = new Array();
}



BxDolPFM.prototype.init = function() {
	//generate areas
    for (var iInd = 1; iInd <= this.config.areas; iInd ++) { //we will begin ID's from 1 !
        var area = new BxDolPFMArea(this, iInd);
        if (!area.element)
            continue;
        
        this.areas[this.areas.length] = area;
    }
}

BxDolPFM.prototype.getAreaByID = function( iAreaID ) {
	for( var iAreaInd = 0; iAreaInd < this.areas.length; iAreaInd ++ )
		if( this.areas[iAreaInd].id == iAreaID )
			return this.areas[iAreaInd];
	
	return false;
}

BxDolPFM.prototype.updateAreas = function( sText, iItemID, sNewName, iAreaID ) {
	switch( sText ) {
		case 'deleteItem':
			location.reload();
		break;
		
		case 'updateItem':
			for( var iAreaInd = 0; iAreaInd < this.areas.length; iAreaInd ++ )
				this.areas[iAreaInd].getItemOrBlockByID( iItemID ).updateName( sNewName );
		break;
		
		case 'newBlock':
			if( !iItemID ) {
				alert( 'Sorry couldn\'t insert new block. Please check if NEW BLOCK already exists.' );
				this.getAreaByID( iAreaID ).moveFakeBlockBack();
			} else {
				for( var iAreaInd = 0; iAreaInd < this.areas.length; iAreaInd ++ ) {
					var oArea = this.areas[iAreaInd];
					
					oArea.insertNewBlock( iItemID );
					oArea.moveFakeBlockBack();
					
					if( oArea.id == iAreaID )
						oArea.resortAndSubmit();
					else
						oArea.resortArrays(); // do not submit positions for other areas
				}
			}
		break;
			
		case 'newItem':
			if( !iItemID ) {
				alert( 'Sorry couldn\'t insert new item. Please check if NEW_ITEM already exists.' );
				this.getAreaByID( iAreaID ).moveFakeItemBack();
			} else {
				for( var iAreaInd = 0; iAreaInd < this.areas.length; iAreaInd ++ ) {
					var oArea = this.areas[iAreaInd];
					
					oArea.insertNewItem( iItemID );
					oArea.moveFakeItemBack();
					
					if( oArea.id == iAreaID )
						oArea.resortAndSubmit();
					else
						oArea.resortArrays(); // do not submit positions for other areas
				}
			}
		break;
	}
}




/* * * * Area Class * * * */

function BxDolPFMArea(parent, id) {
	this.id     = id;
	this.parent = parent;
	
    this.getElement();
    
    if (!this.element)
        return false;
    
	
	this.active_blocks      = new Array();
	this.inactive_blocks    = new Array();
	this.active_items       = new Array();
	this.inactive_items     = new Array();
	
	this.activeZoneElemID         = 'area_' + this.id + '_active'
	this.inactiveItemsZoneElemID  = 'area_' + this.id + '_items_inactive'
	this.inactiveBlocksZoneElemID = 'area_' + this.id + '_blocks_inactive'
	
	this.requestData();
}

BxDolPFMArea.prototype.getElement = function() {
	this.element = this.parent.config.getAreaElem(this.id);
}

BxDolPFMArea.prototype.requestData = function() {
	var oThisArea = this;
	$.getJSON(
		this.parent.config.parserUrl,
		{action: 'getArea', id: this.id},
		function(oAreaData){
			oThisArea.getData(oAreaData);
		}
	);
}

BxDolPFMArea.prototype.getData = function(oAreaData) {
	if( this.id != oAreaData.id )
		return false;
	
	for( var iBlockInd = 0; iBlockInd < oAreaData.active_blocks.length;   iBlockInd++ )    this.active_blocks[   this.active_blocks.length   ] = new BxDolPFMBlock( this, oAreaData.active_blocks[   iBlockInd ] );
	for( var iBlockInd = 0; iBlockInd < oAreaData.inactive_blocks.length; iBlockInd++ )    this.inactive_blocks[ this.inactive_blocks.length ] = new BxDolPFMBlock( this, oAreaData.inactive_blocks[ iBlockInd ] );
	for( var iItemInd  = 0; iItemInd  < oAreaData.active_items.length;    iItemInd ++ )    this.active_items[    this.active_items.length    ] = new BxDolPFMItem(  this, oAreaData.active_items[    iItemInd  ] );
	for( var iItemInd  = 0; iItemInd  < oAreaData.inactive_items.length;  iItemInd ++ )    this.inactive_items[  this.inactive_items.length  ] = new BxDolPFMItem(  this, oAreaData.inactive_items[  iItemInd  ] );
	
	this.draw();
}

BxDolPFMArea.prototype.draw = function() {
	$( this.element ).html( '' ); //clear element
    var sActiveItemsC = _t('_adm_mbuilder_active_items');
    var sInactiveItemsC = _t('_adm_mbuilder_inactive_items');
    var sInactiveBlocksC = _t('_adm_txt_pb_inactive_blocks');

	$( this.element ).append(
		'<div class="build_zone_header">'+sActiveItemsC+'</div>' +
		'<div class="blocks_cont_bord">' +
			'<div class="blocks_container" id="' + this.activeZoneElemID + '">' +
				'<div class="build_block_fake"></div>' +
			'</div>' +
		'</div>' +
		'<br style="height:20px;clear:both;" />' +
		'<div class="build_zone_header">'+sInactiveBlocksC+'</div>' +
		'<div class="blocks_cont_bord">' +
			'<div class="blocks_container" id="' + this.inactiveBlocksZoneElemID + '">' +
				'<div class="build_block_fake"></div>' +
			'</div>' +
		'</div>' +
		'<br style="height:20px;clear:both;" />' +
		'<div class="build_zone_header">'+sInactiveItemsC+'</div>' +
		'<div class="blocks_cont_bord build_block_inactive_items">' +
			'<div class="blocks_container" id="' + this.inactiveItemsZoneElemID + '">' +
				'<div class="build_inac_items_col" id="build_inac_items_area_' + this.id + '_col_1">' +
					'<div class="build_item_fake"></div>' +
				'</div>' +
				'<div class="build_inac_items_col" id="build_inac_items_area_' + this.id + '_col_2">' +
					'<div class="build_item_fake"></div>' +
				'</div>' +
				'<div class="build_inac_items_col" id="build_inac_items_area_' + this.id + '_col_3">' +
					'<div class="build_item_fake"></div>' +
				'</div>' +
				'<div class="build_inac_items_col" id="build_inac_items_area_' + this.id + '_col_4">' +
					'<div class="build_item_fake"></div>' +
				'</div>' +
				'<div class="build_inac_items_col" id="build_inac_items_area_' + this.id + '_col_5">' +
					'<div class="build_item_fake"></div>' +
				'</div>' +
			'</div>' +
		'</div>'
	);
	
	this.activeZoneElement         = $( '#' + this.activeZoneElemID         ).get(0);
	this.inactiveBlocksZoneElement = $( '#' + this.inactiveBlocksZoneElemID ).get(0);
	this.inactiveItemsZoneElement  = $( '#' + this.inactiveItemsZoneElemID  ).get(0);
	
	//draw all active blocks
	for( var iBlockInd = 0; iBlockInd < this.active_blocks.length; iBlockInd ++ ) {
		this.active_blocks[iBlockInd].draw( this.activeZoneElement );
		
		//draw subitems of block
		for( var iItemInd = 0; iItemInd < this.active_items.length; iItemInd ++ )
			if( this.active_items[iItemInd].block == this.active_blocks[iBlockInd].id )
				this.active_items[iItemInd].draw( this.active_blocks[iBlockInd].element );
	}
	
	//append NEW BLOCK element
	$( this.inactiveBlocksZoneElement ).append(
		'<div class="build_block" id="build_block_new_' + this.id + '">' +
			'<div class="build_block_header"><i class="sys-icon arrows"></i>NEW BLOCK</div>' +
		'</div>'
	);
	
	//append all inactive blocks
	for( var iBlockInd = 0; iBlockInd < this.inactive_blocks.length; iBlockInd ++ )
		this.inactive_blocks[iBlockInd].draw( this.inactiveBlocksZoneElement );
	
	//append NEW_ITEM element
	$( '#build_inac_items_area_' + this.id + '_col_1' ).append(
		'<div class="build_item_active" id="build_item_new_' + this.id + '"><i class="sys-icon arrows"></i>NEW_ITEM</div>'
	);
	
	//append all inactive items to 5 columns
	var iColCount = 2;
	for( var iItemInd = 0; iItemInd < this.inactive_items.length; iItemInd ++ ) {
		this.inactive_items[iItemInd].draw(
			$( '#build_inac_items_area_' + this.id + '_col_' + iColCount++ ).get(0)
		);
		
		if( iColCount > 5 ) iColCount = 1;
	}
	
	$( this.activeZoneElement         ).append( '<div class="clear_both"></div>' );
	$( this.inactiveBlocksZoneElement ).append( '<div class="clear_both"></div>' );
	$( this.inactiveItemsZoneElement  ).append( '<div class="clear_both"></div>' );
	
	this.fixZonesWidths();
	this.activateSortable();
}

BxDolPFMArea.prototype.fixZonesWidths = function() {
	if( this.active_blocks.length ) {
		//fix active area
		var el = $( this.active_blocks[0].element );
		
		var w1 = parseInt( el.css( 'width'        ) ) | 0;
		var w2 = parseInt( el.css( 'margin-left'  ) ) | 0;
		var w3 = parseInt( el.css( 'margin-right' ) ) | 0;
		var w = ( w1 + w2 + w3 ) * ( this.active_blocks.length + 1 ) + 20;
		$( this.activeZoneElement ).parent().width( w );
	}
	
	if( this.inactive_blocks.length ) {
		//fix inactive area
		var el = $( this.inactive_blocks[0].element );
		
		var w1 = parseInt( el.css( 'width'        ) ) | 0;
		var w2 = parseInt( el.css( 'margin-left'  ) ) | 0;
		var w3 = parseInt( el.css( 'margin-right' ) ) | 0;
		var w = ( w1 + w2 + w3 ) * ( this.inactive_blocks.length + 2 ) + 20;
		$( this.inactiveBlocksZoneElement ).parent().width( w );
	}
}


BxDolPFMArea.prototype.activateSortable = function() {
	var oThisArea = this;
    
    if ($('.blocks_container', this.element).is('ui-sortable'))
        $('.blocks_container', this.element).sortable('destroy');
    $('.blocks_container', this.element).sortable({
        items: $('.build_block,.build_block_fake', this.element),
        //connectWith: $('.blocks_container', this.element),
        cancel: '.build_item_active,.build_item_inactive',
        dropOnEmpty: false,
        placeholder: 'build_block ui-sortable-placeholder',
        forcePlaceholderSize: true,
		stop: function(e,ui){
			oThisArea.stopItemsSort( ui.item );
		}
    });
    
    $('.build_block,.build_inac_items_col', this.element).sortable({
        items: $('.build_item_active,.build_item_inactive,.build_item_fake', this.element),
        dropOnEmpty: false,
        placeholder: 'build_item_active  ui-sortable-placeholder',
        forcePlaceholderSize: true,
		stop: function(e,ui){
			oThisArea.stopItemsSort( ui.item );
		}
    });
}

BxDolPFMArea.prototype.stopItemsSort = function( item ) {
    var draggedElementID = $(item).attr('id');
	if( draggedElementID == 'build_block_new_' + this.id ) {
		if( $( '#build_block_new_' + this.id ).parent().is( '#' + this.activeZoneElemID ) )
			this.createNewBlock();
	}
	else if( draggedElementID == 'build_item_new_' + this.id ){
		if( $( '#build_item_new_' + this.id ).parents('#' + this.activeZoneElemID).length )
			this.createNewItem();
	}
	else
		this.resortAndSubmit();
}

BxDolPFMArea.prototype.resortAndSubmit = function(){
	var oThisArea = this;
	
	setTimeout( function() {
		oThisArea.resortArrays();
		oThisArea.submitPositions();
	}, 550 );
}

BxDolPFMArea.prototype.createNewBlock = function() {
	var oThisArea = this;
	
	$.getJSON(
		this.parent.config.parserUrl,
		{action: 'createNewBlock'},
		function(oAreaData){
			oThisArea.parent.updateAreas( 'newBlock', oAreaData.id, '', oThisArea.id );
		}
	);
}

BxDolPFMArea.prototype.createNewItem = function() {
	var oThisArea = this;
	
	$.getJSON(
		this.parent.config.parserUrl,
		{action: 'createNewItem'},
		function(oAreaData){
			oThisArea.parent.updateAreas( 'newItem', oAreaData.id, '', oThisArea.id );
		}
	);
}

BxDolPFMArea.prototype.insertNewBlock = function( newBlockID ) {
	
	var iNewInd = this.active_blocks.length;
	var oNewBlockData = {id:newBlockID,name:'NEW BLOCK'};
	var oThisArea = this;
	
	//create object
	this.active_blocks[iNewInd] = new BxDolPFMBlock( this, oNewBlockData );
	var oNewBlock = this.active_blocks[iNewInd];
	
	//insert before fake NEW BLOCK element
	$( oNewBlock.getCode() ).insertBefore( '#build_block_new_' + this.id );
	
	//attach onclick event for link
	$( '#' + oNewBlock.elementID + ' > div.build_block_header' ).children( 'a' ).click( function(){
		oThisArea.openFieldDialog( oNewBlock.id, oThisArea.id );
	} );
	
	//get element
	oNewBlock.getElement();
	
	this.activateSortable();
}

BxDolPFMArea.prototype.insertNewItem = function( newItemID ) {
	var iNewInd = this.active_items.length;
	var oNewItemData = {id:newItemID,name:'NEW_ITEM'};
	var oThisArea = this;
	
	//create object
	this.active_items[iNewInd] = new BxDolPFMItem( this, oNewItemData );
	var oNewItem = this.active_items[iNewInd];
	
	//insert before fake NEW_ITEM element
	$( oNewItem.getCode() ).insertBefore( '#build_item_new_' + this.id );
	
	//attach onclick event for link
	$( '#' + oNewItem.elementID ).children( 'a' ).click( function(){
		oThisArea.openFieldDialog( oNewItem.id, oThisArea.id );
	} );
	
	//get element
	this.active_items[iNewInd].getElement();
	
	this.activateSortable();
}

BxDolPFMArea.prototype.moveFakeBlockBack = function() {
	$( '#build_block_new_' + this.id ).prependTo( this.inactiveBlocksZoneElement );
}

BxDolPFMArea.prototype.moveFakeItemBack = function() {
	$( '#build_item_new_' + this.id ).prependTo( '#build_inac_items_area_' + this.id + '_col_1' );
}

BxDolPFMArea.prototype.resortArrays = function() {
	var oThisArea = this;
	
	var aNewBlocks     = new Array();
	var aNewBlocksInac = new Array();
	var aNewItems      = new Array();
	var aNewItemsInac  = new Array();
	
	//get active blocks
	$( '#' + this.activeZoneElemID + ' .build_block' ).each( function( ind, eBlock ){
		var oBlock = oThisArea.getBlockByElementID( eBlock.id );
		if( !oBlock || $( eBlock ).css('visibility') == 'hidden' ) return; //just in case
		aNewBlocks.push( oBlock );
		
		//get active items of this block
		$( '#' + oBlock.elementID + ' .build_item_active' ).each( function( ind, eItem ){
			var oItem = oThisArea.getItemByElementID( eItem.id );
			if( !oItem || $( eItem ).css('visibility') == 'hidden' ) return; //it can be fake NEW_ITEM
			oItem.block = oBlock.id; //set parent block
			aNewItems.push( oItem );
		});
	});
	
	//get inactive blocks
	$( '#' + this.inactiveBlocksZoneElemID + ' .build_block' ).each( function( ind, eBlock ){
		var oBlock = oThisArea.getBlockByElementID( eBlock.id );
		if( !oBlock || $( eBlock ).css('visibility') == 'hidden' ) return;
		aNewBlocksInac.push( oBlock );
	});
	
	//get inactive items from blocks zone
	$( '#' + this.inactiveBlocksZoneElemID + ' .build_item_active' ).each( function( ind, eItem ){
		var oItem = oThisArea.getItemByElementID( eItem.id );
		if( !oItem || $( eItem ).css('visibility') == 'hidden' ) return;
		oItem.block = 0;
		aNewItemsInac.push( oItem );
	});
	
	//get inactive items from items zone
	$( '#' + this.inactiveItemsZoneElemID + ' .build_item_active' ).each( function( ind, eItem ){
		var oItem = oThisArea.getItemByElementID( eItem.id );
		if( !oItem || $( eItem ).css('visibility') == 'hidden' ) return;
		oItem.block = 0;
		aNewItemsInac.push( oItem );
	});
	
	this.active_blocks   = aNewBlocks;
	this.inactive_blocks = aNewBlocksInac;
	this.active_items    = aNewItems;
	this.inactive_items  = aNewItemsInac;
	
	this.fixZonesWidths();
}

BxDolPFMArea.prototype.submitPositions = function(){
	var oRequest = {};
	
	for( var iBlockInd = 0; iBlockInd < this.active_blocks.length; iBlockInd ++ ) {
		oRequest['blocks[' + iBlockInd + ']'] = this.active_blocks[iBlockInd].id;
	}
	
	for( var iItemInd = 0; iItemInd < this.active_items.length; iItemInd ++ ) {
		oRequest['items[' + iItemInd + ']'] = this.active_items[iItemInd].id;
		oRequest['items_blocks[' + this.active_items[iItemInd].id + ']'] = this.active_items[iItemInd].block;
	}
	
	oRequest.action = 'savePositions';
	oRequest.id = this.id;
	
	var oThisArea = this;
	$.post( this.parent.config.parserUrl, oRequest, function(sResult){oThisArea.processSaveResult(sResult);} );
}

BxDolPFMArea.prototype.processSaveResult = function( sResult ) {
	if( $.trim( sResult ) != 'OK' )
		alert( sResult );
}

BxDolPFMArea.prototype.getBlockByElementID = function( getID ) {
	for( var iBlockInd = 0; iBlockInd < this.active_blocks.length; iBlockInd ++ )
		if( this.active_blocks[iBlockInd].elementID == getID )
			return this.active_blocks[iBlockInd];
	
	for( var iBlockInd = 0; iBlockInd < this.inactive_blocks.length; iBlockInd ++ )
		if( this.inactive_blocks[iBlockInd].elementID == getID )
			return this.inactive_blocks[iBlockInd];
	
	return false;
}

BxDolPFMArea.prototype.getItemByElementID = function( getID ) {
	for( var iItemInd = 0; iItemInd < this.active_items.length; iItemInd ++ )
		if( this.active_items[iItemInd].elementID == getID )
			return this.active_items[iItemInd];
	
	for( var iItemInd = 0; iItemInd < this.inactive_items.length; iItemInd ++ )
		if( this.inactive_items[iItemInd].elementID == getID )
			return this.inactive_items[iItemInd];
	
	return false;
}

BxDolPFMArea.prototype.getItemOrBlockByID = function( getID ) {
	//search in active items
	for( var iItemInd = 0; iItemInd < this.active_items.length; iItemInd ++ )
		if( this.active_items[iItemInd].id == getID )
			return this.active_items[iItemInd];
	
	//search in inactive items
	for( var iItemInd = 0; iItemInd < this.inactive_items.length; iItemInd ++ )
		if( this.inactive_items[iItemInd].id == getID )
			return this.inactive_items[iItemInd];
	
	//search in active blocks
	for( var iBlockInd = 0; iBlockInd < this.active_blocks.length; iBlockInd ++ )
		if( this.active_blocks[iBlockInd].id == getID )
			return this.active_blocks[iBlockInd];
	
	//search in inactive blocks
	for( var iBlockInd = 0; iBlockInd < this.inactive_blocks.length; iBlockInd ++ )
		if( this.inactive_blocks[iBlockInd].id == getID )
			return this.inactive_blocks[iBlockInd];
	
	return false;
};

BxDolPFMArea.prototype.openFieldDialog = function( iItemID, iAreaID ) {
	var oDate = new Date();
	var sHolderId = 'edit_form_cont';
	$('#' + sHolderId).load(
		this.parent.config.parserUrl,
        {
			action: 'loadEditForm',
			id: iItemID,
			area: iAreaID,
        	_t:oDate.getTime()
        },
        function() {
        	var oPopup = $(this).children('div:first').hide();

        	oPopup.dolPopup({
                closeOnOuterClick: false,
        		onHide: function() {
        			oPopup.remove();
        		}
        	});
        }
    );
};


BxDolPFMArea.prototype.getHorizScroll = function() {
	return (navigator.appName == "Microsoft Internet Explorer") ? document.documentElement.scrollLeft : window.pageXOffset;
}

BxDolPFMArea.prototype.getVertScroll = function() {
	return (navigator.appName == "Microsoft Internet Explorer") ? document.documentElement.scrollTop : window.pageYOffset;
}


/* * * * Block Class * * * */

function BxDolPFMBlock( parent, oBlockData ) {
	this.id        = oBlockData.id;
	this.parent    = parent;
	this.name      = oBlockData.name;
	this.elementID = 'build_block_' + this.parent.id + '_' + this.id;
}

BxDolPFMBlock.prototype.draw = function( oParentElement ) {
	var oThisBlock = this;
	
	$( oParentElement ).append( this.getCode() );
	$( '#' + this.elementID + ' > div.build_block_header' ).children( 'a' ).click( function(){
		oThisBlock.parent.openFieldDialog( oThisBlock.id, oThisBlock.parent.id );
	} );
	
	this.getElement();
}

BxDolPFMBlock.prototype.getCode = function() {
	return '<div class="build_block" id="' + this.elementID + '">' +
			'<div class="build_block_header">' +
				'<i class="sys-icon arrows"></i>' +
				'<a href="javascript:void(0)">' +
					this.name +
				'</a>' +
			'</div>' +
			'<div class="build_item_fake"></div>' +
		'</div>';
}

BxDolPFMBlock.prototype.getElement = function() {
	this.element = $( '#' + this.elementID ).get(0);
}

BxDolPFMBlock.prototype.updateName = function( sNewName ) {
	$( this.element ).children( 'div.build_block_header' ).children( 'a' ).html( sNewName );
}

/* * * * Item Class * * * */

function BxDolPFMItem( parent, oItemData ) {
	this.id        = oItemData.id;
	this.parent    = parent;
	this.name      = oItemData.name;
	this.block     = oItemData.block;
	
	this.elementID = 'build_item_' + this.parent.id + '_' + this.id;
}

BxDolPFMItem.prototype.draw = function( oParentElement ) {
	var oThisItem = this;
	
	$( oParentElement ).append( this.getCode() );
	$( '#' + this.elementID ).children( 'a' ).click( function(){
		oThisItem.parent.openFieldDialog( oThisItem.id, oThisItem.parent.id );
	} );
	
	this.getElement();
}

BxDolPFMItem.prototype.getCode = function( oParentElement ) {
	return '<div class="build_item_active" id="' + this.elementID + '">' +
		'<i class="sys-icon arrows"></i>' + 
		'<a href="javascript:void(0)">' +
			this.name +
		'</a>' +
	'</div>';
}
BxDolPFMItem.prototype.getElement = function() {
	this.element = $( '#' + this.elementID ).get(0);
}

BxDolPFMItem.prototype.updateName = function( sNewName ) {
	$( this.element ).children( 'a' ).html( sNewName );
}

/* * * * Non-class functions * * * */



function hideEditForm() {
	$('#pf_edit_popup').dolPopupHide();
}


function changeFieldType( _type ) {
	var aShow = new Array();
	var aHide = new Array();	
	switch( _type ) {
		case 'text':
			aShow = new Array( 'field_minimum', 'field_maximum', 'field_unique', 'field_check', 'field_minimum_msg', 'field_maximum_msg', 'field_unique_msg', 'field_check_msg', 'field_default' );
			aHide = new Array( 'field_control_select_one', 'field_control_select_set', 'field_values', 'field_lkey' );
		break;
		case 'area':
			aShow = new Array( 'field_minimum', 'field_maximum', 'field_unique', 'field_check', 'field_minimum_msg', 'field_maximum_msg', 'field_unique_msg', 'field_check_msg' );
			aHide = new Array( 'field_control_select_one', 'field_control_select_set', 'field_values', 'field_default', 'field_lkey' );
		break;
		case 'pass':
			aShow = new Array( 'field_minimum', 'field_maximum', 'field_check', 'field_minimum_msg', 'field_maximum_msg', 'field_check_msg' );
			aHide = new Array( 'field_control_select_one', 'field_control_select_set', 'field_unique', 'field_unique_msg', 'field_values', 'field_default', 'field_lkey' );
		break;
		case 'date':
			aShow = new Array( 'field_minimum', 'field_maximum', 'field_check', 'field_minimum_msg', 'field_maximum_msg', 'field_check_msg', 'field_default' );
			aHide = new Array( 'field_control_select_one', 'field_control_select_set', 'field_unique', 'field_unique_msg', 'field_values', 'field_lkey' );
		break;
		case 'select_one':
			aShow = new Array( 'field_control_select_one', 'field_values', 'field_default', 'field_lkey' );
			aHide = new Array( 'field_minimum', 'field_maximum', 'field_unique', 'field_check', 'field_control_select_set', 'field_minimum_msg', 'field_maximum_msg', 'field_unique_msg', 'field_check_msg' );
		break;
		case 'select_set':
			aShow = new Array( 'field_control_select_set', 'field_values', 'field_lkey' );
			aHide = new Array( 'field_minimum', 'field_maximum', 'field_control_select_one', 'field_default', 'field_unique', 'field_check', 'field_minimum_msg', 'field_maximum_msg', 'field_unique_msg', 'field_check_msg' );
		break;
		case 'num':
			aShow = new Array( 'field_minimum', 'field_maximum', 'field_unique', 'field_check', 'field_minimum_msg', 'field_maximum_msg', 'field_unique_msg', 'field_check_msg', 'field_default' );
			aHide = new Array( 'field_control_select_one', 'field_control_select_set', 'field_values', 'field_lkey' );
		break;
		case 'range':
			aShow = new Array( 'field_minimum', 'field_maximum', 'field_check', 'field_minimum_msg', 'field_maximum_msg', 'field_check_msg', 'field_default' );
			aHide = new Array( 'field_control_select_one', 'field_control_select_set', 'field_unique', 'field_unique_msg', 'field_values', 'field_lkey' );
		break;
		case 'bool':
			aShow = new Array( 'field_default');
			aHide = new Array( 'field_control_select_one', 'field_control_select_set', 'field_unique', 'field_minimum', 'field_maximum', 'field_check', 'field_minimum_msg', 'field_maximum_msg', 'field_unique_msg', 'field_check_msg', 'field_values', 'field_lkey' );
		break;
	}

	for( var iInd = 0; iInd < aHide.length; iInd ++ )
		$( '#' + aHide[iInd] ).css( {display: 'none'} );
	
	for( var iInd = 0; iInd < aShow.length; iInd ++ )
		$( '#' + aShow[iInd] ).css( {display: ''} );

}

function activateValuesEdit( eLink ) {
	$( eLink ).parent().html( '<textarea class="input_text" name="' +
		$( eLink ).siblings( 'input' ).attr( 'name' ) +
		'">' +
		$( eLink ).siblings( 'input' ).val() +
		'</textarea>'
	);
}

function clearFormErrors( eForm ) {
	$( 'td.warned', eForm ).removeClass( 'warned' ).next( 'td' ).children( 'img.depr_icon' ).remove();
}

function genEditFormError( sField, sText ) {
	if( document.forms.fieldEditForm[sField] ) {
        var eInfoCont = $( document.forms.fieldEditForm[sField] ).parent( 'td' ).next( 'td' );
        eInfoCont.find('.depr_icon').remove();
		eInfoCont.prepend(
			'<img src="../templates/base/images/icons/depr.gif" class="depr_icon" ' + 
			'onmouseover="showFloatDesc(\'' + processFloatDescInput( sText ) + '\')" ' +
			'onmousemove="moveFloatDesc( event )" ' +
			'onmouseout="hideFloatDesc()" ' +
			' />'
		);
	}
}

function updateBuilder( sText, iItemID, sNewName ) {
	oPFM.updateAreas( sText, iItemID, sNewName );
}

function processFloatDescInput( sText ) {
	sText = sText.replace( /&/g, '&amp;' );
	sText = sText.replace( /</g, '&lt;' );
	sText = sText.replace( />/g, '&gt;' );
	sText = sText.replace( /"/g, '&quot;' );
	sText = sText.replace( /\\/g, '\\\\' );
	sText = sText.replace( /'/g, '\\\'' );
	
	return sText;
}

