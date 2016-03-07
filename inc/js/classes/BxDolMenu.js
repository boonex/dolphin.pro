function BxDolMenu( idContainer, urlParser, aTopItems, aCustomItems, aSystemItems, aInactiveItems, aCoords, e )
{
	if( !e )
		e = window.event;
	
	this.container   = document.getElementById( idContainer );
	this.urlParser   = urlParser;
	
	this.iStartX     = aCoords['startX'];
	this.iStartY     = aCoords['startY'];
	this.iItemWidth  = aCoords['width'];
	this.iItemHeight = aCoords['height'];
	this.iDiffX      = aCoords['diffX'];
	this.iDiffY      = aCoords['diffY'];
	
	this.aItems         = new Array();
	this.aTopItems      = new Array();
	this.aCustomItems   = new Array();
	this.aInactiveItems = new Array();
	
	this.iMaxActiveID = 0;
	
	this.dragObj = false;
	
	this.iCounterX = 0;
	this.aCounterY = new Array();
	this.iMaxCounterY = 0;
	
	for( id in aTopItems ) //create top items
	{
		var oNewItem = new BxDolMenuItem( this, aTopItems[id][0], 'top', aTopItems[id][1], this.iCounterX, 0, 0, aTopItems[id][2]);
		
		//put to arrays
		this.aItems.push( oNewItem );
		this.aTopItems[this.iCounterX]     = oNewItem;
		
		this.aCustomItems[this.iCounterX] = new Array();
		var iCounterY = 0;
		
		for( cid in aCustomItems[id] ) //create custom items
		{
			var oNewCItem = new BxDolMenuItem( this, aCustomItems[id][cid][0], 'custom', aCustomItems[id][cid][1], this.iCounterX, (iCounterY + 1), 0, aCustomItems[id][cid][2]);
			
			//put to arrays
			this.aItems.push( oNewCItem );
			this.aCustomItems[this.iCounterX][iCounterY] = oNewCItem;
			
			iCounterY ++;
		}
		
		this.aCounterY[this.iCounterX] = iCounterY;
		if( this.iMaxCounterY < iCounterY )
			this.iMaxCounterY = iCounterY;
		
		this.iCounterX ++;
	}
	
	for( id in aSystemItems ) //create system items
	{
		var oNewSItem = new BxDolMenuItem( this, aSystemItems[id][0], 'system', aSystemItems[id][1], this.iCounterX, 0, 0, aSystemItems[id][2]);
		
		//put to arrays
		this.aItems.push( oNewSItem );
		this.aTopItems[this.iCounterX]     = oNewSItem;
		
		this.aCustomItems[this.iCounterX] = new Array();
		var iCounterY = 0;
		
		for( cid in aCustomItems[id] ) //create custom items
		{
			var oNewCItem = new BxDolMenuItem( this, aCustomItems[id][cid][0], 'custom', aCustomItems[id][cid][1], this.iCounterX, (iCounterY + 1), 0, aCustomItems[id][cid][2]);
			
			//put to arrays
			this.aItems.push( oNewCItem );
			this.aCustomItems[this.iCounterX][iCounterY] = oNewCItem;
			
			iCounterY ++;
		}
		
		this.aCounterY[this.iCounterX] = iCounterY;
		if( this.iMaxCounterY < iCounterY )
			this.iMaxCounterY = iCounterY;
		
		this.iCounterX ++;
	}
	
	this.iInactiveStartY = this.iMaxCounterY + 3;
	this.iInactivePerRow = iInactivePerRow;
	this.iInactiveRowCounter = 0;
	
	var iInactiveCounterX = 1;
	
	if( allowNewItem )
	{
		var oNewIItem = new BxDolMenuItem( this, 0, 'inactive', sNewItemTitle, 1, this.iInactiveStartY, 0 );
		this.aItems.push( oNewIItem );
		this.aInactiveItems.push( oNewIItem );
		iInactiveCounterX ++;
	}
	
	var iInactiveCounterY = this.iInactiveStartY;
	
	this.iMaxActiveID += 1000; // this is for difference between active and inactive items
	
	for( id in aInactiveItems ) // create inactive items
	{
		var oNewIItem = new BxDolMenuItem( this, ( this.iMaxActiveID + 1 ), 'inactive', aInactiveItems[id], iInactiveCounterX, iInactiveCounterY, parseInt(id) );
		
		this.aItems.push( oNewIItem );
		this.aInactiveItems.push( oNewIItem );
		
		iInactiveCounterX ++;
		if( iInactiveCounterX >= this.iInactivePerRow )
		{
			iInactiveCounterX = 0;
			this.iInactiveRowCounter ++;
			iInactiveCounterY ++;
		}
	}
	
	if( iInactiveCounterX != 0 )
		this.iInactiveRowCounter ++; //not full row
	
	this.movIndic = new BxDolMenuMovIndic( this, 'mov_indic' );
	this.pseudo1  = document.getElementById( 'pseudo1' );
	this.pseudo2  = document.getElementById( 'pseudo2' );
	
	this.resizePseudos();
	
	var $this = this;
	
	addEvent( document, 'mousemove', function( e ){ mouseMoveEvent( e, $this ); } );
	
	addEvent( document, 'selectstart', function(){return false} );
	
	//addEvent( window, 'click', function(){return true} );
}

BxDolMenu.prototype.checkCollisions = function()
{
	var obj = this.dragObj;
	
	if( obj.type == 'top' && obj.dragToCellY == 0 && obj.dragToCellX >= 0 && obj.dragToCellX < this.iCounterX )
	{
		//move top item inside top menu
	}
	else if( obj.type == 'system' && obj.dragToCellY == 0 && obj.dragToCellX >= 0 && obj.dragToCellX < this.iCounterX )
	{
		//move system item inside top menu
	}
	else if( obj.type == 'top'    && obj.dragToCellY > 0  && allowAddToCustom && obj.dragToCellX >= 0 && obj.dragToCellX < ( this.iCounterX - 1 ) && obj.dragToCellY <= ( /*this.aCounterY[obj.dragToCellX] + */ 1 ) )
	{
		//move top item to custom menu
	}
	else if( obj.type == 'custom' && obj.dragToCellY == 0 && allowAddToTop && obj.dragToCellX >= 0 && obj.dragToCellX < ( this.iCounterX + 1) )
	{
		//move custom item to top menu
	}
	else if( obj.type == 'custom' && obj.dragToCellY > 0  && obj.dragToCellX == obj.cellX && obj.dragToCellY < ( this.aCounterY[obj.dragToCellX] + 1 ) )
	{
		//move custom item inside column
	}
	else if( obj.type == 'custom' && obj.dragToCellY > 0  && obj.dragToCellX >= 0 && obj.dragToCellX != obj.cellX && obj.dragToCellX < this.iCounterX && obj.dragToCellY < ( this.aCounterY[obj.dragToCellX] + 2 ) )
	{
		//move custom to other column
	}
	else if( obj.type == 'inactive' && obj.dragToCellY > 0 && allowAddToCustom && obj.dragToCellX >= 0 && obj.dragToCellX < this.iCounterX && obj.dragToCellY < ( this.aCounterY[obj.dragToCellX] + 2 ) )
	{
		//move inactive to custom
	}
	else if( obj.type == 'inactive' && obj.dragToCellY == 0 && allowAddToTop && obj.dragToCellX >= 0 && obj.dragToCellX < ( this.iCounterX + 1 ) )
	{
		//move inactive to top
	}
	else if( ( obj.type == 'top' || obj.type == 'custom' )
		&& obj.dragToCellX >= 0 /* && obj.dragToCellX < this.iInactivePerRow */
		&& obj.dragToCellY >= this.iInactiveStartY && obj.dragToCellY < ( this.iInactiveStartY + this.iInactiveRowCounter ) )
	{
		//move top or custom to inactive
		obj.dragToCellX = 0;
		obj.dragToCellY = this.iInactiveStartY;
	}
	else
	{
		obj.dragToCellX = obj.cellX;
		obj.dragToCellY = obj.cellY;
	}
}

BxDolMenu.prototype.redrawItems = function()
{
	//move top items
	var iTempCounterX = 0;
	for( var ord = 0; ord < this.iCounterX; ord ++ )
	{
		var obj = this.aTopItems[ord];
		if( !obj )
			continue;
		
		if( obj.id == this.dragObj.id )
		{
			obj.tempCellX = obj.dragToCellX;
			obj.tempCellY = obj.dragToCellY;
		}
		else
		{
			if( iTempCounterX == this.dragObj.dragToCellX && this.dragObj.dragToCellY == 0 )
				iTempCounterX ++;
			
			obj.tempCellX = iTempCounterX;
			obj.tempCellY = 0;
			
			obj.element.style.left = ( this.iStartX + ( this.iDiffX * obj.tempCellX ) ) + 'px';
			obj.element.style.top  = ( this.iStartY + ( this.iDiffX * obj.tempCellY ) ) + 'px';
			
			iTempCounterX ++;
		}
	}
	
	
	//move custom items
	var iTempCounterX = 0;
	for( var ord = 0; ord < this.iCounterX; ord ++ )
	{
		var iTempCounterY = 1;
		for( cord = 0; cord < this.aCounterY[ord]; cord ++ )
		{
			var obj = this.aCustomItems[ord][cord];
			if( !obj )
				continue;
			
			if( obj.id == this.dragObj.id )
			{
				obj.tempCellX = obj.dragToCellX;
				obj.tempCellY = obj.dragToCellY;
			}
			else
			{
				if( this.dragObj.type == 'custom' && this.dragObj.dragToCellY > 0 ) // custom item is dragged inside columns
				{
					if( iTempCounterX == this.dragObj.dragToCellX && iTempCounterY == this.dragObj.dragToCellY )
						iTempCounterY ++;
					
					obj.tempCellX = iTempCounterX;
					obj.tempCellY = iTempCounterY;
				}
				else if( this.dragObj.type == 'top' || this.dragObj.dragToCellY == 0 ) // top item dragged or something dragged to top
				{
					if( this.dragObj.type == 'top' && this.dragObj.dragToCellY > 0 && obj.cellX == this.dragObj.cellX ) // top item dragged down
					{
						//hide item's custom menu
						obj.element.style.display = 'none';
						continue; //pass counting Y
					}
					
					obj.element.style.display = 'block';
					
					if( this.aTopItems[obj.cellX].tempCellX == this.dragObj.dragToCellX && iTempCounterY == this.dragObj.dragToCellY )
						iTempCounterY ++;
					
					//move custom items to new positions
					obj.tempCellX = this.aTopItems[obj.cellX].tempCellX;
					obj.tempCellY = iTempCounterY;
				}
				else if( this.dragObj.type == 'inactive' ) // inactive item is dragged
				{
					if( iTempCounterX == this.dragObj.dragToCellX && iTempCounterY == this.dragObj.dragToCellY )
						iTempCounterY ++;
					
					obj.tempCellX = iTempCounterX;
					obj.tempCellY = iTempCounterY;
				}
				
				obj.element.style.left = ( this.iStartX + ( this.iDiffX * obj.tempCellX ) ) + 'px';
				obj.element.style.top  = ( this.iStartY + ( this.iDiffY * obj.tempCellY ) ) + 'px';
				
				iTempCounterY ++;
			}
		}
		iTempCounterX ++;
	}
	
	//move inactive items. do not move
	for( ord in this.aInactiveItems )
	{
		var obj = this.aInactiveItems[ord];
		if( !obj )
			continue;
		
		if( obj.id == this.dragObj.id )
		{
			obj.tempCellX = obj.dragToCellX;
			obj.tempCellY = obj.dragToCellY;
			//obj.element.innerHTML = obj.dragToCellX + ' ' + obj.dragToCellY;
		}
		else
		{
			obj.tempCellX = obj.cellX;
			obj.tempCellY = obj.cellY;
		}
	}
}

BxDolMenu.prototype.resortItemsArrs = function()
{
	//resort arrays
	
	var aNewTopItems = new Array();
	var aNewCustomItems = new Array();
	
	var iNewCounterX = 0;
	var aNewCounterY = new Array();
	
	var iItemsNum = this.aItems.length;
	
	for( ind = 0; ind < iItemsNum; ind ++ )
	{
		var obj = this.aItems[ind];
		if( !obj )
			continue;
		
		if( obj.element.style.display == 'none' )
		{
			obj.element.parentNode.removeChild( obj.element );
			obj = undefined;
			this.aItems[ind] = undefined;
			continue;
		}
		
		obj.cellX = obj.tempCellX;
		obj.cellY = obj.tempCellY;
		
		if( obj.cellY == 0 && obj.type != 'inactive' )
		{
			aNewTopItems[obj.cellX] = obj;
			
			if( obj.type != 'system' )
			{
				obj.type = 'top';
				obj.element.className = 'top_item';
			}
			
			iNewCounterX ++;
		}
		else if( obj.cellY > 0 && obj.cellY <= ( this.iMaxCounterY + 1 ) && obj.type != 'inactive' )
		{
			if( typeof( aNewCustomItems[obj.cellX] ) == 'undefined' )
				aNewCustomItems[obj.cellX] = new Array();
			
			aNewCustomItems[obj.cellX][obj.cellY - 1] = obj;
			obj.type = 'custom';
			obj.element.className = 'custom_item';
			
			if( typeof( aNewCounterY[obj.cellX] ) == 'undefined' )
				aNewCounterY[obj.cellX] = 1;
			else
				aNewCounterY[obj.cellX] ++;
		}
		else if( obj.cellY == 0 && obj.type == 'inactive' )
		{
			var newObjID = createNewItem( 'top', obj.sourceID );
			if( !newObjID )
			{
				alert( _t('_adm_mbuilder_Sorry_could_not_insert_object') );
				continue;
			}
			
			var newObj = new BxDolMenuItem( this, newObjID, 'top', obj.title, obj.cellX, 0 );
			
			aNewTopItems[newObj.cellX] = newObj;
			this.aItems.push( newObj );
			iNewCounterX ++;
		}
		else if( obj.cellY > 0 && obj.cellY <= ( this.iMaxCounterY + 1 ) && obj.type == 'inactive' )
		{
			if( typeof( aNewCustomItems[obj.cellX] ) == 'undefined' )
				aNewCustomItems[obj.cellX] = new Array();
			
			var newObjID = createNewItem( 'custom', obj.sourceID );
			if( !newObjID )
			{
				alert( _t('_adm_mbuilder_Sorry_could_not_insert_object') );
				continue;
			}
			
			var newObj = new BxDolMenuItem( this, newObjID, 'custom', obj.title, obj.cellX, obj.cellY );
			
			aNewCustomItems[newObj.cellX][newObj.cellY - 1] = newObj;
			this.aItems.push( newObj );
			
			if( typeof( aNewCounterY[newObj.cellX] ) == 'undefined' )
				aNewCounterY[newObj.cellX] = 1;
			else
				aNewCounterY[newObj.cellX] ++;
		}
		else if( ( obj.type == 'custom' || obj.type == 'top' ) && obj.cellX == 0 && obj.cellY == this.iInactiveStartY )
		{
			//deactivate top or custom
			deactivateItem( obj.id );
			obj.element.parentNode.removeChild( obj.element );
			obj = undefined;
			this.aItems[ind] = undefined;
		}
	}
	
	//apply arrays
	
	this.aTopItems = new Array();
	this.aCustomItems = new Array();
	
	this.iCounterX = iNewCounterX;
	this.aCounterY = new Array();
	this.iMaxCounterY = 0;
	
	for( ord = 0; ord < this.iCounterX; ord ++ )
	{
		this.aTopItems[ord] = aNewTopItems[ord];
		
		if( typeof( aNewCounterY[ord] ) == 'undefined' )
			this.aCounterY[ord] = 0;
		else
			this.aCounterY[ord] = aNewCounterY[ord];
		
		if( this.iMaxCounterY < this.aCounterY[ord] )
			this.iMaxCounterY = this.aCounterY[ord]
		
		this.aCustomItems[ord] = new Array();
		for( cord = 0; cord < this.aCounterY[ord]; cord ++ )
		{
			this.aCustomItems[ord][cord] = aNewCustomItems[ord][cord];
		}
	}
	
	this.iInactiveStartY = this.iMaxCounterY + 3;
	this.saveItemsArrs();
	this.redrawInactiveItems();
	this.resizePseudos();
}

BxDolMenu.prototype.saveItemsArrs = function()
{
	var sTopItems = '';
	var aCustomItems = new Array();
	
	for( ord = 0; ord < this.iCounterX; ord ++ )
	{
		var obj = this.aTopItems[ord];
		if( !obj )
			continue;
		
		if( obj.type == 'top' || ( obj.type == 'system' && sendSystemOrder ) )
			sTopItems += obj.id + ',';
		
		aCustomItems[obj.id] = '';
		for( cord = 0; cord < this.aCounterY[ord]; cord ++ )
		{
			var cobj = this.aCustomItems[ord][cord];
			if( !cobj )
				continue;
			
			aCustomItems[obj.id] += cobj.id + ',';
		}
	}
	
	saveItemsOrders( sTopItems, aCustomItems );
}

BxDolMenu.prototype.resizePseudos = function()
{
	this.pseudo1.innerHTML = '';
	if( allowAddToTop )
		this.pseudo1.style.width  = ( ( this.iCounterX + 1 ) * this.iDiffX ) + 'px';
	else
		this.pseudo1.style.width  = ( this.iCounterX * this.iDiffX ) + 'px';
    if(parseInt(this.pseudo1.style.width) + 330 > $('body').width()) {
    	var iTableWidth = parseInt(this.pseudo1.style.width) + 330;
    	$('table.adm-middle').width(iTableWidth);
        $('div.adm-header, div.adm-middle').width(iTableWidth + 20);
    }
	this.pseudo1.style.height = ( ( this.iMaxCounterY + 2 ) * this.iDiffY + 10) + 'px';
	

	this.pseudo2.innerHTML = '';
	this.pseudo2.style.width  = ( ( this.iInactivePerRow     ) * this.iDiffX + 10) + 'px';
	this.pseudo2.style.height = ( ( this.iInactiveRowCounter ) * this.iDiffY + 10) + 'px';
	
	if( _main_cont = document.getElementById( 'main_cont' ) )
		_main_cont.style.height = ( ( this.iMaxCounterY + this.iInactiveRowCounter + 2 ) * this.iDiffY ) + 115 + 'px';

	$('.pseudo_body.pseudo_body_empty').removeClass('pseudo_body_empty');
}

BxDolMenu.prototype.redrawInactiveItems = function()
{
	var iInactiveCounterX = 1;
	var iInactiveCounterY = this.iInactiveStartY;
	
	for( ind in this.aInactiveItems )
	{
		obj = this.aInactiveItems[ind];
		if( !obj )
			continue;
		
		obj.cellX = iInactiveCounterX;
		obj.cellY = iInactiveCounterY;
		
		obj.element.style.left = ( this.iStartX + ( obj.cellX * this.iDiffX ) ) + 'px';
		obj.element.style.top  = ( this.iStartY + ( obj.cellY * this.iDiffY ) ) + 'px';
		
		iInactiveCounterX ++;
		if( iInactiveCounterX >= this.iInactivePerRow )
		{
			iInactiveCounterX = 0;
			iInactiveCounterY ++;
		}
	}
}

BxDolMenu.prototype.updateItem = function( id, title )
{
	if( this.editObj.id == id )
		this.editObj.setNewTitle( title );
	
	this.editObj = false;
}

BxDolMenu.prototype.deleteItem = function( id )
{
	if( this.editObj.id == id )
	{
		this.dragObj = this.editObj;
		
		this.dragObj.dragToCellX = 0;
		this.dragObj.dragToCellY = this.iInactiveStartY;
		
		this.redrawItems();
		this.resortItemsArrs();
		
		this.dragObj = false;
	}
}



function BxDolMenuItem( parentObj, id, type, title, cellX, cellY, sourceID, movable)
{
	
	this.parent = parentObj;
	this.id     = parseInt( id );
	this.type   = type;
	this.title  = title;
	this.cellX  = cellX;
	this.cellY  = cellY;
	this.sourceID = sourceID;
	this.movable = !movable ? 3 : parseInt(movable);
	
	this.width  = this.parent.iItemWidth;
	this.height = this.parent.iItemHeight;
	this.diffX  = this.parent.iDiffX;
	this.diffY  = this.parent.iDiffY;
	
	this.container = this.parent.container;

	this.prevCellX  = this.cellX; //for save possibility
	this.prevCellY  = this.cellX;
	
	if( this.parent.iMaxActiveID < this.id )
		this.parent.iMaxActiveID = this.id;
	
	this.draw();
}

BxDolMenuItem.prototype.draw = function()
{
	var newElem = document.createElement( 'DIV' );

	newElem.id = 'menu_item_' + this.id;
	
	newElem.className = this.type + '_item';
	
	newElem.style.width    = this.width + 'px';
	newElem.style.height   = this.height + 'px';
	newElem.style.left     = ( this.parent.iStartX + ( this.parent.iDiffX * this.cellX ) ) + 'px';
	newElem.style.top      = ( this.parent.iStartY + ( this.parent.iDiffY * this.cellY ) ) + 'px';
	newElem.style.zIndex  = '5';
	
	var $this = this;
	
	var aHref = document.createElement( 'A' );
	aHref.href = 'javascript:void(0);';
	aHref.innerHTML = this.title; // put name
    aHref.title = this.title;

	addEvent( aHref, 'click', function( e ){ itemClickEvent( e, $this ); } );

	var aIcon = document.createElement( 'I' );
    aIcon.className = 'sys-icon arrows';

    addEvent( aIcon, 'mousedown', function( e ){ itemEvent( e, $this ); } );
	addEvent( aIcon, 'mouseup',   function( e ){ itemEvent( e, $this ); } );

	newElem.appendChild( aIcon );
	newElem.appendChild( aHref );

	this.container.appendChild( newElem );

	this.element = document.getElementById( newElem.id );
}

BxDolMenuItem.prototype.dragStart = function( m )
{
	this.element.style.opacity = '0.7';
	this.element.style.filter  = 'alpha(opacity=70)';
	this.element.style.zIndex  = '10';
	
	this.parent.dragObj = this;
	
	
	this.dragOffsetX   = m.x - parseInt( this.element.style.left );
	this.dragOffsetY   = m.y - parseInt( this.element.style.top );
	
	this.parent.movIndic.show();
	this.drag( m );
}

BxDolMenuItem.prototype.drag = function(m) {
	//Movable with X dimension(horizontally)
	if(this.movable & 1) {
		this.element.style.left = ( m.x - this.dragOffsetX ) + 'px';
		this.dragToCellX = Math.floor( ( m.x - this.parent.iStartX ) / this.diffX );
	}
	//Movable with Y dimension(vertically)
	if(this.movable & 2) {
		this.element.style.top  = ( m.y - this.dragOffsetY ) + 'px';
		this.dragToCellY = Math.floor( ( m.y - this.parent.iStartY ) / this.diffY );
	}

	this.parent.checkCollisions();
	this.parent.redrawItems();

	this.parent.movIndic.move();
}

BxDolMenuItem.prototype.dragStop = function( m )
{
	if( !this.parent.dragObj )
		return false;
	
	this.element.style.opacity = '';
	this.element.style.filter  = '';
	this.element.style.zIndex  = '5';
	
	this.parent.dragObj = false;
	
	
	this.cellX = this.dragToCellX;
	this.cellY = this.dragToCellY;
	
	
	this.parent.movIndic.hide();
	this.moveToCell();
}

BxDolMenuItem.prototype.moveToCell = function()
{
	var step = 10;
	var timeout = 10;
	
	var destX = this.parent.iStartX + ( this.cellX * this.diffX );
	var destY = this.parent.iStartY + ( this.cellY * this.diffY );
	
	var curX = parseInt( this.element.style.left );
	var curY = parseInt( this.element.style.top );

	if( curX != destX || curY != destY ) {
		if(Math.abs( curX - destX ) > step)
			var needMoveX = ( Math.abs( curX - destX ) / ( curX - destX ) ); // +1 or -1
		else {
			var needMoveX = 0;
			this.element.style.left = destX + 'px';
		}

		if( Math.abs( curY - destY ) > step )
			var needMoveY = ( Math.abs( curY - destY ) / ( curY - destY ) ); // +1 or -1
		else {
			var needMoveY = 0;
			this.element.style.top = destY + 'px';
		}

		if( needMoveX != 0 && needMoveY != 0 ) //proportional move
		{
			var movRatio = Math.abs( ( curX - destX ) / ( curY -destY ) );
			if( movRatio > 1 )
			{
				var stepX = step;
				var stepY = step / movRatio;
			}
			else
			{
				var stepX = step * movRatio;
				var stepY = step;
			}
			
			this.element.style.left = Math.round( curX - needMoveX * stepX ) + 'px';
			this.element.style.top  = Math.round( curY - needMoveY * stepY ) + 'px';
		}
		else if( needMoveX != 0 ) //move X only
			this.element.style.left = ( curX - needMoveX * step ) + 'px';
		else if( needMoveY != 0 ) //move Y only
			this.element.style.top  = ( curY - needMoveY * step ) + 'px';
		
		$this = this;
		setTimeout( '$this.moveToCell();', timeout );
	}
	else
	{
		this.parent.resortItemsArrs();
	}

}

BxDolMenuItem.prototype.setNewTitle = function( title )
{
	this.title = title;
	
	this.element.innerHTML = '';
	
	var aHref = document.createElement( 'A' );
	aHref.href = 'javascript:void(0);';
	aHref.innerHTML = this.title; // put name
	
	var $this = this;
	addEvent( aHref, 'click', function( e ){ itemClickEvent( e, $this ); } );
	
	this.element.appendChild( aHref );
}




function BxDolMenuMovIndic( parent, elId )
{
	this.parent = parent;
	this.width  = parent.iItemWidth;
	this.height = parent.iItemHeight;
	
	var newElem = document.createElement( 'DIV' );
	
	newElem.id = elId;
	newElem.className = 'mov_indic';
	newElem.style.width = this.width + 'px';
	newElem.style.height = this.height + 'px';
	newElem.style.zIndex = '1';
	newElem.style.display = 'none';
	
	parent.container.appendChild( newElem );
	
	this.element = document.getElementById( elId );
}

BxDolMenuMovIndic.prototype.show = function()
{
	this.element.style.display = 'block';
}

BxDolMenuMovIndic.prototype.hide = function()
{
	this.element.style.display = 'none';
}

BxDolMenuMovIndic.prototype.move = function() {
	var p = this.parent;

	//Movable with X dimension(horizontally)
	if(p.dragObj.movable & 1)
		this.element.style.left = (p.iStartX + p.dragObj.dragToCellX * p.iDiffX) + 'px';
	else 
		this.element.style.left = p.iStartX + 'px';

	//Movable with Y dimension(vertically)
	if(p.dragObj.movable & 2)
		this.element.style.top  = (p.iStartY + p.dragObj.dragToCellY * p.iDiffY) + 'px';
	else
		this.element.style.top  = p.iStartY + 'px';
}


function mouseCoord( evt )
{
	var pos_X = 0, pos_Y = 0;

	if ( typeof(evt.pageX) == 'number' )
	{
		pos_X = evt.pageX;
		pos_Y = evt.pageY;
	}
	else if ( typeof(evt.clientX) == 'number' )
	{
		pos_X = evt.clientX;
		pos_Y = evt.clientY;
		
		if ( document.body && 
			( document.body.scrollTop || document.body.scrollLeft ) && 
			!( window.opera || window.debug || navigator.vendor == 'KDE' ) )
		{
			pos_X += document.body.scrollLeft;
			pos_Y += document.body.scrollTop;
		}
		else if ( document.documentElement &&
			( document.documentElement.scrollTop ||
			document.documentElement.scrollLeft ) &&
			!( window.opera || window.debug || navigator.vendor == 'KDE' ) )
		{
			pos_X += document.documentElement.scrollLeft;
			pos_Y += document.documentElement.scrollTop;
		}
	}

	var oParentPosition = $(evt.target).parents('.items_wrapper').offset();

	pos_X -= oParentPosition.left;
	pos_Y -= oParentPosition.top;

	return new function() {
		this.x = pos_X;
		this.y = pos_Y;
	};
}

function itemEvent( e, obj )
{
	if( !e )
		e = window.event;

	var target = $(e.target || e.srcElement).parent().get(0);

	if( obj.element.id == target.id )
	{
		var m  = mouseCoord( e );
		switch( e.type )
		{
			case 'mousedown': obj.dragStart( m ); break;
			case 'mouseup':   obj.dragStop( m ); break;
		}
	}
}

function mouseMoveEvent( e, obj )
{
	if( !e )
		e = window.event;
	
	var target = ( e.target || e.srcElement );
	
	if( obj.dragObj )
	{
		var m  = mouseCoord( e );
		obj.dragObj.drag( m );
	}
}

function itemClickEvent( e, obj )
{
	if(obj.type == 'inactive' || obj.type == 'system') {
		alert(_t('_adm_mbuilder_This_items_are_non_editable'));
		return;
	}

	showItemEditForm(obj.id);
}
