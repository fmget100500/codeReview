<script type="text/javascript">
	
	var iii=1;

	var DataTjBlocksArr = [ <?php echo $SysFunctions->TjDataHtml; ?> ];

	var AllFontsUserArr = [ <?php echo $SysFunctions->fontsUser; ?> ];
	
	var lettersCellCols = [ <?php echo $SysFunctions->ExcelNameCells; ?> ]; // Название ячеек A, B, C, D, E,...
	
	var cacheNum = <?php echo rand(10001, 99999); ?>; // для картинок
	
    (function( window ) {
		
		function asd(i){var ii = i+1; return ii;}

        var activeImage = null, startX = 0, startY = 0, 

		activeSelection = document.createElement( 'span' ),
		html = document.documentElement,
		body = document.body,

		imff = document.getElementById("pdfBlock");

		imgs = imff.getElementsByTagName('div');
		activeSelection.id = 'selectedTj'; // 'my-id'
		activeSelection.style.cssText = "display:block;position:absolute; line-height:0; font-size:0; z-index: 100;"+
						 "background-color:#ffffff; filter:Alpha(opacity=30)"; 
		activeSelection.className = "selectedSpan";                     
		
		
		function cb(x,y) {
		// cb(x,y,w,h)
			var newDiv = document.createElement('div');
			newDiv.className = 'my-classD';
			newDiv.id = 'my-idD';
			newDiv.style.backgroundColor = 'red';

			newDiv.innerHTML = 'x:'+x+'<br>y:'+y;
			
			body.insertBefore( newDiv, body.firstChild );
		} 

        function shiftScroll() {
            return {
                X: ( html && html.scrollLeft || body && body.scrollLeft || 0 ),
                Y: ( html && html.scrollTop || body && body.scrollTop || 0 )
            }
        }

        function fixEvent( e ) {

            e = e || window.event;

            if ( e.pageX == null && e.clientX != null ) {
                e.pageX = e.clientX + shiftScroll().X - ( html.clientLeft || 0 ); // ???
                e.pageY = e.clientY + shiftScroll().Y - ( html.clientTop || 0 ); // ???
				
            }

            if ( !e.which && e.button ) {
                e.which = e.button & 1 ? 1 : ( e.button & 2 ? 3 : ( e.button & 4 ? 2 : 0 ) ); // ???
            }

            return e;
        }
		

        for( var i = 0; i < imgs.length; i++ ) {
            imgs[ i ].onselectstart = imgs[ i ].ondragstart = function() {
                return false;
            }
            imgs[ i ].onmousedown = function( e ) { //!!! при нажатии

                e = fixEvent( e ); 

                activeImage = e.target || e.srcElement; // определение объекта, идентификация что это он же! // window.event
                body.appendChild( activeSelection );
				startX = e.pageX;
                startY = e.pageY; 

                if ( e.preventDefault ) {
                    e.preventDefault(); 
                }
                e.returnValue = false;
            }
        }
		



        document.onmouseup = function( e ) { //!!! при отпускании
           if ( activeImage ) {

                e = fixEvent( e );

                var iRect = activeImage.getBoundingClientRect();
                var sRect = activeSelection.getBoundingClientRect();

                var shift = shiftScroll();
                var X = ( sRect.left + shift.X < startX ? sRect.left + shift.X : startX ) - iRect.left - shift.X;
                var Y = ( sRect.top + shift.Y < startY ? sRect.top + shift.Y : startY ) - iRect.top - shift.Y;

                // тут что-то открываем, делаем

                activeImage = null; // отпускает(останавливает) прямоугольник в конечной точке.

				var thisBlock = document.getElementById("selectedTj");
				var pdfPage = document.getElementById('pdfPage');
				
				var getOffset_pdfPage = getOffset(pdfPage);
				var pdfPageTop = getOffset_pdfPage.top; pdfPageTop = parseFloat(pdfPageTop);
				var pdfPageLeft = getOffset_pdfPage.left; pdfPageLeft = parseFloat(pdfPageLeft);
				
				//alert(pdfPageLeft);
				
				if(iii==1){$("#titleTextName").show(); showFontSettings();}; // появляется скрытая надпись Текстовые блоки для excel:
				var is = iii.toString();
				if(thisBlock!=null){
					
					var getOffsetPdf = getOffset(thisBlock);

					// Параметры выделенной области (прямоугольника)
					var thisTop = getOffsetPdf.top; thisTop = parseFloat(thisTop); thisTop = thisTop - pdfPageTop;
					var thisLeft = getOffsetPdf.left; thisLeft = parseFloat(thisLeft); thisLeft = thisLeft - pdfPageLeft;
					var thisWidth = $('#selectedTj').width(); thisWidth = parseFloat(thisWidth);
					var thisHeight = $('#selectedTj').height(); thisHeight = parseFloat(thisHeight);

					var existingSpan = pdfPage.getElementsByTagName('span'); // выделенные области пользователем (если есть)
					
					var existClone = 1;
					var allTjNumInSelectedBlocks = $('#allTjNumInSelectedBlocks').html(); // записываем номера Tj 
						allTjNumInSelectedBlocks = allTjNumInSelectedBlocks.toString();
					var issetTjNumInSelectedBlocks = 1; // есть ли уже в выделенном блоке какие Tj
					
					// проверка на существование такой же области. Чтобы не дублировать!
					for (var i = 0; i < existingSpan.length; i++) {
						
						var el = existingSpan[i];

						var top = el.style.top; top = parseFloat(top); 
						var left = el.style.left; left = parseFloat(left); 
						var width = el.style.width; width = parseFloat(width);
						var height = el.style.height; height = parseFloat(height); //alert(height);
						if((thisTop == top) && (thisLeft == left) && (thisWidth == width) && (thisHeight == height)){
							existClone = 0; 
						}

					}				
				

					
					
					// определяем только те блоки Tj, которые попали под выделение! 
					var existingDiv = pdfPage.getElementsByTagName('div'); // все блоки Tj
					var tjInFocusArr = []; // номера блоков Tj попавших под выделение  
					var ff = 0; // количество блоков Tj попавших под выделение  

					var percentScaleZoom = getIntchangeScaleProject();
					
					for (var i = 0; i < existingDiv.length; i++) {

						if( i == DataTjBlocksArr.length ){ continue; }
						
						var el = existingDiv[i];

						var top = el.style.top; top = parseFloat(top); //parseInt(top);
						var left = el.style.left; left = parseFloat(left); //alert(left);
						var width = el.style.width; width = parseFloat(width); //alert(width);
						var height = el.style.height; height = parseFloat(height); //alert(height);
						var text = DataTjBlocksArr[i].text; //alert(height);
						var fontName = DataTjBlocksArr[i].fontName;
						var fontSizeW = DataTjBlocksArr[i].fontSizeW;
						var fontSizeH = DataTjBlocksArr[i].fontSizeH;
						// fontName fontSizeW  fontSizeH text
						
        				var tc_ =  DataTjBlocksArr[i].tc;
        				var tw_ = DataTjBlocksArr[i].tw;
        				var tz_ = DataTjBlocksArr[i].tz;
        				var tl_ = DataTjBlocksArr[i].tl;						
        				var color_ = DataTjBlocksArr[i].color;						
        				var colorSpace_ = DataTjBlocksArr[i].colorSpace;
        				var colorOrigAll_ = DataTjBlocksArr[i].colorOrigAll
        				var colorError_ = DataTjBlocksArr[i].colorError
        				
						
						if((thisTop < top) && (thisLeft < left) && (thisWidth > width) && (thisHeight > height) && ((left+width)<(thisLeft+thisWidth)) && ((top+height)<(thisTop+thisHeight)) ){

							tjInFocusArr[ff] = { colorError: colorError_, colorOrigAll: colorOrigAll_, colorSpace: colorSpace_, color: color_, tc: tc_, tw: tw_, tz: tz_, tl: tl_, clas: i, left: left, top: top, width: width, height: height, text: text, fontName: fontName, fontSizeW: fontSizeW, fontSizeH: fontSizeH};

							ff++; // 0 1 2 3 
						}
	
					}						

					// РАБОТАЕМ с блоками внутри выделения!

					var newPageWidth = ( DataTjBlocksArr[0].pageWidth / 100 ) * percentScaleZoom;
					var newPageHeight = ( DataTjBlocksArr[0].pageHeight / 100 ) * percentScaleZoom;
					
					var min_Left_tjInFocus=newPageWidth, max_Left_tjInFocus=0, min_Top_tjInFocus=newPageHeight, max_Top_tjInFocus=0;
					   
					// определяем текст TJ попавших в область выделения!
					var textTj = '';
					var idNumTj = '';
					var fontNameTj = '';
					var fontSizeWTj = '';
					var fontSizeHTj = '';
					var text_tc = '';
					var text_tw = '';
					var text_tz = '';
					var text_tl = '';
					var colorText = '';
					var colorSpace = '';
					var colorOrigAll = '';
					var colorError = '';
					var countTextRows = 1; // подсчет количества строк
					var countTextRowsLastTjid = 0;
					for (var i in tjInFocusArr)
					{
						if((i>0) && (tjInFocusArr[i].top != tjInFocusArr[i-1].top)){textTj += "\r\n"; countTextRows += 1; }
						var symbolArrCode = tjInFocusArr[i].text.split(';');
						for (var sy=0; sy < symbolArrCode.length; sy++){
						   textTj += String.fromCharCode(parseInt(symbolArrCode[sy]));
						}
						fontNameTj = tjInFocusArr[i].fontName;
						fontSizeWTj = tjInFocusArr[i].fontSizeW; // 
						fontSizeHTj = tjInFocusArr[i].fontSizeH;

						text_tc = tjInFocusArr[i].tc;
						text_tw = tjInFocusArr[i].tw;
						text_tz = tjInFocusArr[i].tz;
						text_tl = tjInFocusArr[i].tl;
						colorText = tjInFocusArr[i].color;
						colorOrigAll = tjInFocusArr[i].colorOrigAll;
						colorSpace = tjInFocusArr[i].colorSpace;
						if(tjInFocusArr[i].colorError !== ''){ colorError = tjInFocusArr[i].colorError; } // чтобы не перезаписывались, т.к. в блоке могут быть разные цвета!

						var minLTj = tjInFocusArr[i].left;
						if(minLTj<min_Left_tjInFocus){min_Left_tjInFocus = minLTj;}

						var maxLTj = tjInFocusArr[i].left + tjInFocusArr[i].width; 
						if (maxLTj>max_Left_tjInFocus){max_Left_tjInFocus = maxLTj;}					   

						var minTTj = tjInFocusArr[i].top; //alert(minTTj);
						if(minTTj<min_Top_tjInFocus){min_Top_tjInFocus = minTTj;}

						var maxTTj = tjInFocusArr[i].top + tjInFocusArr[i].height;
						if (maxTTj>max_Top_tjInFocus){max_Top_tjInFocus = maxTTj;}

						idNumTj += tjInFocusArr[i].clas+',';

						countTextRowsLastTjid = tjInFocusArr[i].clas;
					   
						// Находим уже существующие номера Tj в скрытом блоке div  // 5,16,3 и т.д......
						if ( allTjNumInSelectedBlocks.match('(^|\,)('+tjInFocusArr[i].clas.toString()+')(\,)','i') ) {
							issetTjNumInSelectedBlocks = 0; //alert(tjInFocusArr[i].clas);
							allTjNumInSelectedBlocks = '';
							break;
						} else {
							allTjNumInSelectedBlocks = allTjNumInSelectedBlocks + tjInFocusArr[i].clas+',';
							$('#allTjNumInSelectedBlocks').html(allTjNumInSelectedBlocks);							
						}

					}
					var heightGroupTj = max_Top_tjInFocus - min_Top_tjInFocus;
					var WidthGroupTj = max_Left_tjInFocus - min_Left_tjInFocus; 

					var countColumn = $('#paramBlocks #countColumn').html(iii);
	
					// Подсчет межстрочный интервал Tl
					if( countTextRows > 1 ){ 
					    var LastTjHeight = $('#pdfPage #'+countTextRowsLastTjid).attr('data-height');
					    LastTjHeight = parseFloat(LastTjHeight);
					    var origHeightGroupTj = (heightGroupTj / percentScaleZoom * 100); 
					    var origHeightGroupTjTotal = LastTjHeight * countTextRows; 
					    text_tl = ((origHeightGroupTj - origHeightGroupTjTotal) / (countTextRows - 1)) + LastTjHeight; 
					    text_tl = parseFloat(text_tl.toFixed(1)); 
					}
					
				
					// Если в области есть блоки Tj и нет дублирующих выделеннных областей, то:
					if((tjInFocusArr.length > 0) && (existClone==1) && (issetTjNumInSelectedBlocks==1)){

					     // (Тут весь оставшийся код)
						// кнопки для переключения блоков параметров 

						$("#paramBloksDataButt span").removeClass('buttparamBlocksShowActive');
						$("#paramBloksData div.paramBlocks").hide();
						var isMinus = is - 1;
						buttparamBlocksShow = document.createElement( 'span' );
						buttparamBlocksShow.className = is+" "; 
						buttparamBlocksShow.className += "buttparamBlocksShow";	
						buttparamBlocksShow.className += " buttparamBlocksShowActive";
						buttparamBlocksShow.setAttribute( 'onclick', 'buttparamBlocksClick('+ is +');' );
						buttparamBlocksShow.innerHTML = lettersCellCols[isMinus]; 
						var paramBloksDataButtDiv = document.getElementById("paramBloksDataButt");
						paramBloksDataButtDiv.appendChild( buttparamBlocksShow );
						
						// Создаем див для параметров
						selectedParamDiv = document.createElement( 'div' ); 
						selectedParamDiv.className = is+" "; 
						selectedParamDiv.className += "paramBlocks";
						selectedParamDiv.style.cssText = " "; 
						var paramBlockSDiv = document.getElementById("paramBloksData");
						paramBlockSDiv.appendChild( selectedParamDiv );
						
						// изменяем ширину буквенных кнопок  A  B  C  D, чтобы все умещались на одном ряду
						if( is > 7 ){
						    var widthParamButt = (450 - is * 2) / is;
						    //widthParamButt -= 2; // это бордер
						    $(".buttparamBlocksShow").css('width', widthParamButt);
						}						
	
						var divBlOpen110 = '<div style="margin:8px 0 0 0;">';
						var divBlClose = '</div>';
                        var spanBlOpen = divBlOpen110+'<span style="font-size:11px;line-height:22px;">';
                        var spanBlClose = '</span>'+divBlClose;						
						
						
                        var selectFontCurrent = divBlOpen110+'<span style="font-size:9px;color:#000000;">';
                        selectFontCurrent += fontNameTj;
                        selectFontCurrent += '</span>'+divBlClose;
                          
						
						// Создаем select шрифтов для дива параметров
						var selected='';
						var issetFontName = 'border: 2px solid #d00000;'; // 
						
						var optionSelectedParamFonts = '';
						var notFoundFontTitle = '<?php echo PARAM_PLEASE_SELECT_FONT; ?>';
						
                        for (var key in AllFontsUserArr) {
  
                          if(AllFontsUserArr[key].name == fontNameTj){ 
                              selected = 'selected="selected"';
                              issetFontName = 'border: 1px solid #999999;';
                              notFoundFontTitle = '';
                              selectFontCurrent = '';
                          } else {
                              selected='';
                          }
                          optionSelectedParamFonts += '<option value="'+key+'" '+selected+' >';
                          optionSelectedParamFonts += AllFontsUserArr[key].name;
                          optionSelectedParamFonts += '</option>';
                        }
                        var selectFont = divBlOpen110+'<select name="fontsUser" style="'+issetFontName+'" class="fontsUser" title="'+notFoundFontTitle+'" onChange="getSelectFontVal('+is+');">';
                        selectFont += optionSelectedParamFonts;
                        selectFont += '</select>'+divBlClose;
                        
                        
                        
						// Создаем select Color Space для дива параметров
						var coloSpacesAll = Array('', 'CMYK','RGB','GRAY'); 
						var selectedCS='';
						var issetCS = 'border: 2px solid #d00000;';  
						var optionSelectedCS = '';
						var notFoundCS = '<?php echo PARAM_COLOR_SPACE_NOT_FOUND; ?>'; 
						var selectCurrentCS = ''; 
						var divBlocksColorCS = '<div class="colors">';
						var RGBcolor = '';
						var colorArr = colorText.split(' ');
						var C_cs = 0, M_cs = 0, Y_cs = 0, K_cs = 0, R_cs = 0, G_cs = 0, B_cs = 0, GR_cs = 0;
						var showCMYK = 'display:none;', showRGB = 'display:none;', showGRAY = 'display:none;';
						
						for(var cs = 0; cs < coloSpacesAll.length; cs++){
						    
                            if(coloSpacesAll[cs] == colorSpace){ 
                                selectedCS = 'selected="selected"';
                                issetCS = 'border: 1px solid #999999;';
                                notFoundCS = '';
                                selectCurrentCS = 'background-color: RGB(249, 201, 16);';
                                if(colorSpace == "CMYK"){
                                   C_cs = colorArr[0]; M_cs = colorArr[1]; Y_cs = colorArr[2]; K_cs = colorArr[3];
                                   showCMYK = 'display:block;';
                                   //changeCMYK(is);
                                }else if(colorSpace == "RGB"){
                                   R_cs = colorArr[0]; G_cs = colorArr[1]; B_cs = colorArr[2];
                                   showRGB = 'display:block;';
                                   RGBcolor = 'background-color: rgb('+R_cs+','+G_cs+','+B_cs+');';
                                }else if(colorSpace == "GRAY"){
                                   GR_cs = colorArr[0];
                                   showGRAY = 'display:block;';
                                   var newColorRGB = grayToRgb(GR_cs);
                                   RGBcolor = 'background-color: rgb('+newColorRGB+','+newColorRGB+','+newColorRGB+');';
                                } 
                            } else {
                                selectedCS='';
                            }
                            optionSelectedCS += '<option value="'+cs+'" '+selectedCS+' >';
                            optionSelectedCS += coloSpacesAll[cs];
                            optionSelectedCS += '</option>';                            
						}
						
						var divColorColorCS = '<div class="divColorColorCS" style="width:80px;height:50px;border: 1px solid #000000;'+RGBcolor+'"></div>';
                        
						divBlocksColorCS += '<div class="CMYK_cs colorBlocks" style="'+showCMYK+'">'; 
						divBlocksColorCS += '<table cellpadding="0" cellspacing="0" width="100%">';
						divBlocksColorCS += '<tr><td width="60">'+spanBlOpen+'Cyan '+spanBlClose+'</td>'+'<td>'+spanBlOpen+'<input onkeyup="changeCMYK('+is+');" onchange="changeCMYK('+is+');" name="C_cs" style="width:40px;" class="C_cs" type="number" min="0" max="100" value="'+parseInt(C_cs, 10)+'">'+spanBlClose+'</td></tr>';
						divBlocksColorCS += '<tr><td width="60">'+spanBlOpen+'Magenta '+spanBlClose+'</td>'+'<td>'+spanBlOpen+'<input onkeyup="changeCMYK('+is+');" onchange="changeCMYK('+is+');" name="M_cs" style="width:40px;" class="M_cs" type="number" min="0" max="100" value="'+parseInt(M_cs, 10)+'">'+spanBlClose+'</td></tr>';
						divBlocksColorCS += '<tr><td width="60">'+spanBlOpen+'Yellow '+spanBlClose+'</td>'+'<td>'+spanBlOpen+'<input onkeyup="changeCMYK('+is+');" onchange="changeCMYK('+is+');" name="Y_cs" style="width:40px;" class="Y_cs" type="number" min="0" max="100" value="'+parseInt(Y_cs, 10)+'">'+spanBlClose+'</td></tr>';
						divBlocksColorCS += '<tr><td width="60">'+spanBlOpen+'Key(black) '+spanBlClose+'</td>'+'<td>'+spanBlOpen+'<input onkeyup="changeCMYK('+is+');" onchange="changeCMYK('+is+');" name="K_cs" style="width:40px;" class="K_cs" type="number" min="0" max="100" value="'+parseInt(K_cs, 10)+'">'+spanBlClose+'</td></tr>';
						divBlocksColorCS += '</table>'
						divBlocksColorCS += '</div>';
						
						divBlocksColorCS += '<div class="RGB_cs colorBlocks" style="'+showRGB+'">';
						divBlocksColorCS += '<table cellpadding="0" cellspacing="0" width="100%">';
						divBlocksColorCS += '<tr><td width="40">'+spanBlOpen+'Red '+spanBlClose+'</td>'+'<td>'+spanBlOpen+'<input onkeyup="changeRGB('+is+');" onchange="changeRGB('+is+');" name="R_cs" style="width:40px;" class="R_cs" type="number" min="0" max="255" value="'+parseInt(R_cs, 10)+'">'+spanBlClose+'</td></tr>';
						divBlocksColorCS += '<tr><td width="40">'+spanBlOpen+'Green '+spanBlClose+'</td>'+'<td>'+spanBlOpen+'<input onkeyup="changeRGB('+is+');" onchange="changeRGB('+is+');" name="G_cs" style="width:40px;" class="G_cs" type="number" min="0" max="255" value="'+parseInt(G_cs, 10)+'">'+spanBlClose+'</td></tr>';
						divBlocksColorCS += '<tr><td width="40">'+spanBlOpen+'Blue '+spanBlClose+'</td>'+'<td>'+spanBlOpen+'<input onkeyup="changeRGB('+is+');" onchange="changeRGB('+is+');" name="B_cs" style="width:40px;" class="B_cs" type="number" min="0" max="255" value="'+parseInt(B_cs, 10)+'">'+spanBlClose+'</td></tr>';
						divBlocksColorCS += '</table>'
						divBlocksColorCS += '</div>';	
						
						divBlocksColorCS += '<div class="GRAY_cs colorBlocks" style="'+showGRAY+'">'; 
						divBlocksColorCS += '<table cellpadding="0" cellspacing="0" width="100%">';
						divBlocksColorCS += '<tr><td width="30">'+spanBlOpen+'Gray '+spanBlClose+'</td>'+'<td>'+spanBlOpen+'<input onkeyup="changeGRAY('+is+');" onchange="changeGRAY('+is+');" name="GR_cs" style="width:40px;" class="GR_cs" type="number" min="0" max="100" value="'+parseInt(GR_cs, 10)+'">'+spanBlClose+'</td></tr>';
						divBlocksColorCS += '</table>'
						divBlocksColorCS += '</div>';						
						
			
						divBlocksColorCS += '</div>';
                        
                        var selectColorSpace = divBlOpen110+'<select name="colorSpace" style="'+issetCS+'" class="colorSpace" title="'+notFoundCS+'" onChange="getSelectCS('+is+');">';
                        selectColorSpace += optionSelectedCS;
                        selectColorSpace += '</select>'+divBlClose;                        

						var spanVAlignText = divBlOpen110+'<span style="font-size:11px;line-height:22px;"><?php echo PARAM_VERTICAL_ALIGN_TEXT_O; ?></span>'+divBlClose;
						var spanFontName = divBlOpen110+'<span style="font-size:11px;line-height:22px;"><?php echo PARAM_FONT; ?></span>'+divBlClose; // Размер: 
						var spanFontSize = divBlOpen110+'<span style="font-size:11px;line-height:22px;"><?php echo PARAM_FONT_SIZE; ?></span>'+divBlClose; // Размер: 
						var inputFontSize = divBlOpen110+'<input name="inputFontSize" style="width:40px;" class="inputFontSize" type="number" min="0" value="'+fontSizeWTj+'">'+'<span class="systemSmall"> pt</span>'+divBlClose;
						
						var spanColorSpace = divBlOpen110+'<span style="font-size:11px;line-height:22px;"><?php echo PARAM_COLOR_SPACE; ?></span>'+divBlClose; // Размер: 
						
						var spanTc = divBlOpen110+'<span style="font-size:11px;line-height:22px;"><?php echo PARAM_TEXT_TC; ?></span>'+divBlClose; // Размер: 
						var inputTc = divBlOpen110+'<input name="inputTc" style="width:40px;" class="inputTc" type="number" value="'+text_tc+'">'+'<span class="systemSmall"> pt</span>'+divBlClose;
						var spanTl = divBlOpen110+'<span style="font-size:11px;line-height:22px;"><?php echo PARAM_TEXT_TL; ?></span>'+divBlClose; // Размер: 
						var inputTl = divBlOpen110+'<input name="inputTl" style="width:40px;" class="inputTl" type="number" value="'+text_tl+'">'+'<span class="systemSmall"> pt</span>'+divBlClose;						
						
						/* // НЕ подходит, javascript в некоторых случаях создает дроби 91,9999999999999  
						var tte = parseFloat(fontSizeHTj);
						var tty = parseFloat(fontSizeWTj);
						tty = tty / 100; 
						var percentFontSizeH = tte / tty; // fontSizeHTj  percentFontSizeH
						percentFontSizeH = Math.round(percentFontSizeH);
						alert(percentFontSizeH); 
						*/
						var spanFontSizeH = divBlOpen110+'<span style="font-size:11px;line-height:22px;"><?php echo PARAM_FONT_SIZE_WIDTH; ?></span>'+divBlClose;
						var inputFontSizeH = divBlOpen110+'<input name="inputFontSizeH" style="width:40px;" class="inputFontSizeH" type="number" min="0" value="'+fontSizeHTj+'">'+'<span class="systemSmall"> %</span>'+divBlClose;
						
						
						var autoFontSize = divBlOpen110+'<label><span class="systemSmall">'+'<?php echo PARAM_AUTO_FONT_SIZE_MINUS; ?>'+'</span>';
						autoFontSize += '<input onchange="autoFontSize('+is+');" style="vertical-align:middle;" class="autoFontSizeInput" name="autoFontSizeInput" type="checkbox" style="width:15px;height:15px;"></label>';
						autoFontSize += '<span class="tooltipVissible"><img src="/files.php?access=public&type=img&file=tooltip.png&cache='+<?php echo $cacheAttr['change']; ?>+'">';
						autoFontSize += '<span class="tooltipHideRight"><?php echo PARAM_AUTO_FONT_SIZE_TOOLTIP; ?></span></span>';
						autoFontSize += divBlClose;
						// Создаем input для дива параметров
						var inputTextTj = '<textarea class="textArea" name="textArea" rows="4" cols="40">'+textTj+'</textarea>';
						// кнопки выравнивания текста // return textAlign(this);
						textLeft = "textLeft"; textRight = "textRight"; textCenter = "textCenter"; textJust = "textJust";
						textJustifyForce = "textJustifyForce";
						textValignTop = 'textValignTop'; textValignCenter = 'textValignCenter'; textValignBottom = 'textValignBottom';
						var textAlignButtons = '';
						var textVAlignButtons = '';
						var typeTextBlock = '';
						var typeTextBlockSelect = '';

					
						textAlignButtons += '<span onclick="textAlign('+is+', '+textLeft+');" class="buttText '+textLeft+'"><img src="/files.php?access=public&type=img&file=text_left.png&cache='+cacheNum+'" /></span>';
						textAlignButtons += '<span onclick="textAlign('+is+', '+textCenter+');" class="buttText '+textCenter+'"><img src="/files.php?access=public&type=img&file=text_center.png&cache='+cacheNum+'" /></span>';
						textAlignButtons += '<span onclick="textAlign('+is+', '+textRight+');" class="buttText '+textRight+'"><img src="/files.php?access=public&type=img&file=text_right.png&cache='+cacheNum+'" /></span>';
						textAlignButtons += '<span onclick="textAlign('+is+', '+textJust+');" class="buttText '+textJust+'"><img src="/files.php?access=public&type=img&file=text_just.png&cache='+cacheNum+'" /></span>';							
						textAlignButtons += '<span onclick="textAlign('+is+', '+textJustifyForce+');" class="buttText '+textJustifyForce+'"><img src="/files.php?access=public&type=img&file=text_force.png&cache='+cacheNum+'" /></span>';
						
						textVAlignButtons += '<span onclick="textVAlign('+is+', '+textValignTop+');" class="buttText textValignTop textVAlignOnclick" title="<?php echo PARAM_TEXT_VALIGN_TOP_TITLE; ?>"><img src="/files.php?access=public&type=img&file=text_v_top.png&cache='+cacheNum+'" /></span>';
						textVAlignButtons += '<span onclick="textVAlign('+is+', '+textValignCenter+');" class="buttText textValignCenter" title="<?php echo PARAM_TEXT_VALIGN_CENTER_TITLE; ?>"><img src="/files.php?access=public&type=img&file=text_v_center.png&cache='+cacheNum+'" /></span>';
						textVAlignButtons += '<span onclick="textVAlign('+is+', '+textValignBottom+');" class="buttText textValignBottom" title="<?php echo PARAM_TEXT_VALIGN_BOTTOM_TITLE; ?>"><img src="/files.php?access=public&type=img&file=text_v_bottom.png&cache='+cacheNum+'" /></span>';							
						

						typeTextBlock = divBlOpen110+'<span style="font-size:11px;line-height:22px;"><?php echo PARAM_TEXT_SELECTTYPE; ?></span>'+divBlClose;
						typeTextBlockSelect += divBlOpen110+'<select name="typeTextBlock" class="typeTextBlock">'; // onChange="typeTextBlock('+is+');"
						typeTextBlockSelect += '<option value="0" selected="selected" >paragraph</option>';
						typeTextBlockSelect += '<option value="1">none</option>';
						typeTextBlockSelect += '</select>';
						typeTextBlockSelect += ' <span class="tooltipVissible"><img src="/files.php?access=public&type=img&file=tooltip.png&cache='+<?php echo $cacheAttr['change']; ?>+'">';
						typeTextBlockSelect += '<span class="tooltipHideRight"><?php echo PARAM_TEXT_SELECTTYPE_TITLE; ?></span></span>'; 
						typeTextBlockSelect += divBlClose;
						

						
						var paramBlock='';
						
						paramBlock += '<table cellpadding="0" cellspacing="0" width="100%">';
                        paramBlock += '<tr>';
						paramBlock += '<td colspan="2">';
						paramBlock += inputTextTj;
						paramBlock += '<div class="textAlignButtons">'+textAlignButtons+'</div>';
						paramBlock += '';
						paramBlock += '</td>';
						paramBlock += '<td>';
						paramBlock += '';
						paramBlock += '</td>';
						paramBlock += '</tr>';

						paramBlock += '<tr><td colspan="3"><div style="height:10px;"></div></td></tr>';
						paramBlock += '<tr><td width="40">'+spanFontName+'</td><td width="200">'+selectFont+'</td><td>'+selectFontCurrent+'</td></tr>';
						paramBlock += '<tr><td colspan="3"><div style="height:10px;"><hr></div></td></tr>';
						paramBlock += '</table>';
						
						
						
						paramBlock += '<table cellpadding="0" cellspacing="0" width="100%">';
						paramBlock += '<tr><td width="100">'+spanColorSpace+'</td><td width="80">'+selectColorSpace+'</td><td>'+divBlocksColorCS+'</td><td width="100">'+divColorColorCS+'</td></tr>';
						if(colorError !== ''){ 
						    paramBlock += '<tr><td colspan="4"><span style="font-size:9px;line-height:12px;color:#d00000;">'+colorError+'</span></td></tr>';
						}						
						paramBlock += '</table>';

						
						paramBlock += '<div style="height:20px;"></div>';
						
						paramBlock += '<div class="showAdvSettingsButtDiv" style="">'; // КНОПКА Доп. настройки
						paramBlock +=   '<div><hr></div>';
						paramBlock +=   '<span class="showAdvSettingsButt" onclick="showAdvSettings('+is+')" style="">';
						paramBlock +=       '<?php echo PARAM_SHOW_ADV_SETTING_BLOCK; ?>';
						paramBlock +=   '</span>';
						paramBlock +=   '<input type="hidden" name="showAdvSettingsState" class="showAdvSettingsState" value="hide">';
						paramBlock += '</div>';
						
						
						paramBlock += '<div class="showAdvSettings" style="display:none;">'; // КНОПКА Доп. настройки
						
						paramBlock += '<table cellpadding="0" cellspacing="0" width="100%">';
						paramBlock += '<tr><td width="110">'+spanVAlignText+'</td><td>'+'<div class="textVAlignButtons">'+textVAlignButtons+'</div>'+'</td><td>'+''+'</td></tr>';
						paramBlock += '<tr><td width="110">'+spanFontSize+'</td><td>'+inputFontSize+'</td><td>'+''+'</td></tr>';
						paramBlock += '<tr><td width="110">'+spanFontSizeH+'</td><td>'+inputFontSizeH+'</td><td>'+'</td></tr>';
						paramBlock += '<tr><td colspan="2">'+autoFontSize+'</td><td>'+''+'</td></tr>';
						
						paramBlock += '<tr><td width="110">'+typeTextBlock+'</td><td>'+typeTextBlockSelect+'</td><td>'+'</td></tr>';
						paramBlock += '</table>';
						
						
						
						paramBlock += '<table cellpadding="0" cellspacing="0" width="100%">';
						paramBlock += '<tr><td width="150">'+spanTc+'</td><td>'+inputTc+'</td><td>'+''+'</td></tr>'; // text_tc
						paramBlock += '<tr><td width="150">'+spanTl+'</td><td>'+inputTl+'</td><td>'+''+'</td></tr>'; // text_tc
						
						paramBlock += '</table>';
						
					

						var spanBlOpen = divBlOpen110+'<span style="font-size:11px;line-height:22px;">';  // 
						var spanBlClose = '</span>'+divBlClose;

						var inputSelectedTop = divBlOpen110+'<input onkeyup="changeSelect('+is+', \'top\');" onChange="changeSelect('+is+', \'top\');" name="inputSelectedTop" style="width:60px;" class="inputSelectedTop" type="number" value="'+min_Top_tjInFocus+'">'+'<span class="systemSmall"> pt</span>'+divBlClose;
						var inputSelectedLeft = divBlOpen110+'<input onkeyup="changeSelect('+is+', \'left\');" onChange="changeSelect('+is+', \'left\');" name="inputSelectedLeft" style="width:60px;" class="inputSelectedLeft" type="number" value="'+min_Left_tjInFocus+'">'+'<span class="systemSmall"> pt</span>'+divBlClose;
						var inputSelectedWidth = divBlOpen110+'<input onkeyup="changeSelect('+is+', \'width\');" onChange="changeSelect('+is+', \'width\');" name="inputSelectedWidth" style="width:60px;" class="inputSelectedWidth" type="number" value="'+thisWidth+'">'+'<span class="systemSmall"> pt</span>'+divBlClose;
						var inputSelectedHeight = divBlOpen110+'<input onkeyup="changeSelect('+is+', \'height\');" onChange="changeSelect('+is+', \'height\');" name="inputSelectedHeight" style="width:60px;" class="inputSelectedHeight" type="number" value="'+heightGroupTj+'">'+'<span class="systemSmall"> pt</span>'+divBlClose;						
						
						paramBlock += '<table cellpadding="0" cellspacing="0" width="100%">';						
						paramBlock += '<tr>'+'<td colspan="3">'+'<div style="margin:20px 0 10px 0px;"><hr></div>'+'</td>'+'</tr>';
						paramBlock += '<tr>'+'<td colspan="3">'+'<div style="color:#000000;font-size:11px;"><?php echo PARAM_TEXT_BLOCK; ?></div>'+'</td>'+'</tr>';
						paramBlock += '<tr><td width="70">'+spanBlOpen+'<?php echo PARAM_SELECTED_MARGIN_TOP; ?>'+spanBlClose+'</td><td>'+inputSelectedTop+'</td><td>'+''+'</td></tr>';
						paramBlock += '<tr><td width="70">'+spanBlOpen+'<?php echo PARAM_SELECTED_MARGIN_LEFT; ?>'+spanBlClose+'</td><td>'+inputSelectedLeft+'</td><td>'+''+'</td></tr>';
						paramBlock += '<tr><td width="70">'+spanBlOpen+'<?php echo PARAM_SELECTED_WIDTH; ?>'+spanBlClose+'</td><td>'+inputSelectedWidth+'</td><td>'+''+'</td></tr>';
						paramBlock += '<tr><td width="70">'+spanBlOpen+'<?php echo PARAM_SELECTED_HEIGHT; ?>'+spanBlClose+'</td><td>'+inputSelectedHeight+'</td><td>'+''+'</td></tr>';
						
						paramBlock += '<tr>'+'<td colspan="3">'+'<div style="margin:20px 0 10px 0px;"><hr></div>'+'</td>'+'</tr>';
						paramBlock += '<tr><td colspan="2" width="70">'+'<span class="systemSmall">'+'<?php echo PARAM_COLOR_PDF; ?>'+'<b>'+colorOrigAll+'</b></span>'+'</td><td>'+''+'</td></tr>';
		

						// скрытые блоки      percentScaleZoom
						var hiddenBlocks = '<div class="hidden countTj">'+ff+'</div>';
							hiddenBlocks += '<div class="hidden TjNumbers">'+idNumTj+'</div>';

    							hiddenBlocks += '<div class="hidden topMargin">'+(min_Top_tjInFocus / percentScaleZoom * 100)+'</div>';
    							hiddenBlocks += '<div class="hidden leftMargin">'+(min_Left_tjInFocus / percentScaleZoom * 100)+'</div>';
    							hiddenBlocks += '<div class="hidden WidthGroupTj">'+(WidthGroupTj / percentScaleZoom * 100)+'</div>';
    							hiddenBlocks += '<div class="hidden heightGroupTj">'+(heightGroupTj / percentScaleZoom * 100)+'</div>';	
    							hiddenBlocks += '<div class="hidden selectedWidth">'+(thisWidth / percentScaleZoom * 100)+'</div>';	
    							hiddenBlocks += '<div class="hidden selectedLeft">0</div>';	
    							
							hiddenBlocks += '<div class="hidden textAlign">'+textLeft+'</div>';
							hiddenBlocks += '<div class="hidden textVAlign">'+textValignTop+'</div>';
							hiddenBlocks += '<div class="hidden autoFontSize">none</div>';
						

						paramBlock += '<tr>';
						paramBlock += '<td colspan="3">';
						paramBlock += hiddenBlocks;
						paramBlock += '\r\n<div>\r\n<hr></div>';
						paramBlock += '</td>';
						paramBlock += '</tr>';
						paramBlock += '</table>';
						
						
						paramBlock += '</div>';

						$("#paramBloksData ."+is).html(paramBlock);
						
						if( colorSpace == 'CMYK' ){ changeCMYK(is); }


						// копируем блоки из body в pdfPage, только если нет уже такой же области ------------------------
						// И, если есть текстовые блоки Tj попавшие в выделенную область!
						if (existClone==1) {
						    $("#selectedTj").css({"top":thisTop+"px", "left":thisLeft+"px", "width":thisWidth+"px", "height":thisHeight+"px"});
							$("#selectedTj").clone().addClass(is).attr('id', '').appendTo("#pdfPage"); 
							$("#paramBloksData ."+is+" .textLeft").click();
							iii++; // количество выделенных областей, начинается С 1 !!! 
						}
				
					}

				}

            }
		

        }
        
        
        document.onmousemove = function( e ) { //!!! перемещение. Срабатывает множество раз!
            if ( activeImage ) {
                e = fixEvent( e );

                var shift = shiftScroll();
                var rect = activeImage.getBoundingClientRect();
				
                var X = Math.max( e.pageX > startX ? startX : e.pageX, rect.left + shift.X );
                var Y = Math.max( e.pageY > startY ? startY : e.pageY, rect.top + shift.Y );
                var W = Math.min( Math.abs( Math.max( X, e.pageX ) - startX ), rect.right + shift.X - X );
                var H = Math.min( Math.abs( Math.max( Y, e.pageY ) - startY ), rect.bottom + shift.Y - Y );

                activeSelection.style.left = X + "px";
                activeSelection.style.top = Y + "px";
                activeSelection.style.width = W + "px";
                activeSelection.style.height = H + "px";
	
            }
			
        }


 })( window );
	
</script>
