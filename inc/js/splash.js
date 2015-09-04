//set animation timing
var animationDelay = 60000;

//loading bar effect
var barAnimationDelay = 3800;
var barWaiting = barAnimationDelay - 3000;

//letters effect
var lettersDelay = 50;

//type effect
var typeLettersDelay = 150;
var selectionDuration = 500;
var typeAnimationDelay = selectionDuration + 800;

//clip effect 
var revealDuration = 600;
var revealAnimationDelay = 1500;

$(document).ready(function() {
	initHeadline();
});
 
function initHeadline() {
	//insert <i> element for each letter of a changing word
	singleLetters($('.bx-cd-headline.letters').find('b'));
	
	//initialise headline animation
	animateHeadline($('.bx-cd-headline'));
}

function animateHeadline($headlines) {
	var duration = animationDelay;
	$headlines.each(function(){
		var headline = $(this);

		if(headline.hasClass('loading-bar')) {
			duration = barAnimationDelay;
			setTimeout(function(){ headline.find('.bx-cd-words-wrapper').addClass('is-loading') }, barWaiting);
		} else if (headline.hasClass('clip')){
			var spanWrapper = headline.find('.bx-cd-words-wrapper'),
				newWidth = spanWrapper.width() + 10
			spanWrapper.css('width', newWidth);
		} else if (!headline.hasClass('type') ) {
			//assign to .bx-cd-words-wrapper the width of its longest word
			var words = headline.find('.bx-cd-words-wrapper .bx-cd-word'),
				width = 0;
			words.each(function(){
				var wordWidth = $(this).width();
			    if (wordWidth > width) width = wordWidth;
			});
			headline.find('.bx-cd-words-wrapper').css('width', width);
		};

		//trigger animation
		setTimeout(function(){ hideWord( headline.find('.is-visible').eq(0) ) }, duration);
	});
}

function singleLetters($clauses) {
	$clauses.each(function(){
		var clause = $(this);
		var selected = clause.hasClass('is-visible');

		var words = clause.text().split(' ');
		for(i in words) {
		
			var letters = words[i].split('');
			for (j in letters) {
				if(clause.parents('.rotate-2').length > 0) 
					letters[j] = '<em>' + letters[j] + '</em>';

				letters[j] = (selected) ? '<i class="in">' + letters[j] + '</i>': '<i>' + letters[j] + '</i>';
			}
			words[i] = letters.join('');
		}
		var newWords = words.join(' ');

		clause.html(newWords);
	});
}

function hideWord($word) {
	var nextWord = takeNext($word);
	
	if($word.parents('.bx-cd-headline').hasClass('type')) {
		var parentSpan = $word.parent('.bx-cd-words-wrapper');
		parentSpan.addClass('selected').removeClass('waiting');	
		setTimeout(function(){ 
			parentSpan.removeClass('selected'); 
			$word.removeClass('is-visible').addClass('is-hidden').children('i').removeClass('in').addClass('out');
		}, selectionDuration);
		setTimeout(function(){ showWord(nextWord, typeLettersDelay) }, typeAnimationDelay);
	
	} else if($word.parents('.bx-cd-headline').hasClass('letters')) {
		var bool = ($word.children('i').length >= nextWord.children('i').length) ? true : false;
		hideLetter($word.find('i').eq(0), $word, bool, lettersDelay);
		showLetter(nextWord.find('i').eq(0), nextWord, bool, lettersDelay);

	}  else if($word.parents('.bx-cd-headline').hasClass('clip')) {
		$word.parents('.bx-cd-words-wrapper').animate({ width : '2px' }, revealDuration, function(){
			switchWord($word, nextWord);
			showWord(nextWord);
		});

	} else if ($word.parents('.bx-cd-headline').hasClass('loading-bar')){
		$word.parents('.bx-cd-words-wrapper').removeClass('is-loading');
		switchWord($word, nextWord);
		setTimeout(function(){ hideWord(nextWord) }, barAnimationDelay);
		setTimeout(function(){ $word.parents('.bx-cd-words-wrapper').addClass('is-loading') }, barWaiting);

	} else {
		switchWord($word, nextWord);
		setTimeout(function(){ hideWord(nextWord) }, animationDelay);
	}
}

function showWord($word, $duration) {
	if($word.parents('.bx-cd-headline').hasClass('type')) {
		showLetter($word.find('i').eq(0), $word, false, $duration);
		$word.addClass('is-visible').removeClass('is-hidden');

	}  else if($word.parents('.bx-cd-headline').hasClass('clip')) {
		$word.parents('.bx-cd-words-wrapper').animate({ 'width' : $word.width() + 10 }, revealDuration, function(){ 
			setTimeout(function(){ hideWord($word) }, revealAnimationDelay); 
		});
	}
}

function hideLetter($letter, $word, $bool, $duration) {
	$letter.removeClass('in').addClass('out');
	
	if(!$letter.is(':last-child')) {
	 	setTimeout(function(){ hideLetter($letter.next(), $word, $bool, $duration); }, $duration);  
	} else if($bool) { 
	 	setTimeout(function(){ hideWord(takeNext($word)) }, animationDelay);
	}

	if($letter.is(':last-child') && $('html').hasClass('no-csstransitions')) {
		var nextWord = takeNext($word);
		switchWord($word, nextWord);
	} 
}

function showLetter($letter, $word, $bool, $duration) {
	$letter.addClass('in').removeClass('out');
	
	if(!$letter.is(':last-child')) { 
		setTimeout(function(){ showLetter($letter.next(), $word, $bool, $duration); }, $duration); 
	} else { 
		if($word.parents('.bx-cd-headline').hasClass('type')) { setTimeout(function(){ $word.parents('.bx-cd-words-wrapper').addClass('waiting'); }, 200);}
		if(!$bool) { setTimeout(function(){ hideWord($word) }, animationDelay) }
	}
}

function takeNext($word) {
	return (!$word.is(':last-child')) ? $word.next() : $word.parent().children().eq(0);
}

function takePrev($word) {
	return (!$word.is(':first-child')) ? $word.prev() : $word.parent().children().last();
}

function switchWord($oldWord, $newWord) {
	$oldWord.removeClass('is-visible').addClass('is-hidden');
	$newWord.removeClass('is-hidden').addClass('is-visible');
}