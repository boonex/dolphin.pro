function BxDolVoting (oOptions)
{
	this._sSystem = oOptions.sSystem;
	this._iObjId = oOptions.iObjId;
	this._sUrl = oOptions.sBaseUrl + 'vote.php';

	this._iSize = oOptions.iSize;
	this._iMax = oOptions.iMax;

	this._sHtmlId = oOptions.sHtmlId;

	this._iSaveWidth = -1;
}

BxDolVoting.prototype.over = function (i)
{
	var oSlider = $('#' + this._sHtmlId + ' .votes_slider');
	this._iSaveWidth = parseInt(oSlider.width());
	oSlider.width(i * this._iSize);	
};

BxDolVoting.prototype.out = function ()
{
	var oSlider = $('#' + this._sHtmlId + ' .votes_slider');
	oSlider.width(parseInt(this._iSaveWidth));
};

BxDolVoting.prototype.setRate = function (fRate)
{
	var oSlider = $('#' + this._sHtmlId + ' .votes_slider');
	oSlider.width(fRate * this._iSize);
};

BxDolVoting.prototype.setCount = function (iCount)
{
	$('#' + this._sHtmlId + ' .votes_count i').html(iCount);
};

BxDolVoting.prototype.vote = function (i)
{
	var $this = this;
	var oData = this._getDefaultActions();
	oData['vote_send_result'] = i;

	jQuery.post(this._sUrl, oData, function(oData) {
		if(!oData || oData.rate == undefined || oData.count == undefined) {
			$this.onvotefail();
			return;	
		}

		$this._iSaveWidth = i * $this._iSize;

		$this.setRate(oData.rate);		
		$this.setCount(oData.count);

        $this.onvote(oData.rate, oData.count);
	}, 'json');
};

BxDolVoting.prototype.onvote = function(fRate, iCount) {};
BxDolVoting.prototype.onvotefail = function() {};

BxDolVoting.prototype._getDefaultActions = function() {
    return {
    	'sys': this._sSystem,
    	'id': this._iObjId
    };
};
