<?php
/**
 * Title: Print a HTML Table to PDF file
 * Class: PDFTable
 * Author: vietcom (vncommando at yahoo dot com)
 * Version: 1.95
 * History: pdftable.log
**/
require_once("pdf/fpdf.inc.php");
require_once("pdf/htmlparser.inc.php");

define('FHR',0.58);
define('PDFTABLE_VERSION','1.95');
/** 
$PDF_ALIGN  = array('left'=>'L','center'=>'C','right'=>'R','justify'=>'J');
$PDF_VALIGN = array('top'=>'T','middle'=>'M','bottom'=>'B');
**/
class PDFTable extends FPDF{
	var $left;			//Toa do le trai cua trang
	var $right;			//Toa do le phai cua trang
	var $top;			//Toa do le tren cua trang
	var $bottom;		//Toa do le duoi cua trang
	var $width;			//Width of writable zone of page
	var $height;		//Height of writable zone of page
	var $defaultFontFamily ;
	var $defaultFontStyle;
	var $defaultFontSize;
	var $isNotYetSetFont;
	var $headerTable, $footerTable;
	var $paddingCell = 1; //(mm)
	var $paddingCell2 = 2; //2*$paddingCell
	var $spacingLine = 0; //(mm)
	var $spacingParagraph = 0; //(mm)
	/*tambahan */
	var $HeightCell = 2;

	function _PDFTable($orientation='P',$unit='mm',$format='F4'){
		parent::_FPDF($orientation,$unit,$format);
		$this->SetMargins(15,10,15); //default saja
		$this->SetAuthor('ITGMP');
		$this->_makePageSize();
		$this->isNotYetSetFont = true;
		$this->headerTable = $this->footerTable = '';
	}

	function SetPadding($s=1){
		$this->paddingCell = $s;
		$this->paddingCell2 = 2*$s;
	}
	function SetSpacing($linespacing=1,$paragraphspacing=2){
		$this->spacingLine = $linespacing;
		$this->spacingParagraph = $paragraphspacing;
	}
	function SetMargins($left,$top,$right=null,$bottom=null){
		parent::SetMargins($left, $top, $right);
		$this->bMargin = $bottom?$bottom:$top;
		$this->_makePageSize();
	}

	function SetLeftMargin($margin){
		parent::SetLeftMargin($margin);
		$this->_makePageSize();
	}
	
	function SetRightMargin($margin){
		parent::SetRightMargin($margin);
		$this->_makePageSize();
	}
	
	function SetHeaderFooter($header='',$footer=''){
		if ($header) $this->headerTable = $header;
		if ($footer) $this->footerTable = $footer;
	}
	
	function Header(){
		$this->_makePageSize();
		if ($this->headerTable){
			$this->x = $this->left;
			$this->y = 0;
			$this->htmltable($this->headerTable,0);
		}
	}
	
	function Footer(){
		if ($this->footerTable){
			$this->x = $this->left;
			$this->y = $this->bottom;
			$this->htmltable($this->footerTable,0);
		}
	}
	
	private function _makePageSize(){
		$this->left		= $this->lMargin;
		$this->right	= $this->w - $this->rMargin;
		$this->top		= $this->tMargin;
		$this->bottom	= $this->h - $this->bMargin;
		$this->width	= $this->right - $this->left;
		$this->height	= $this->bottom - $this->tMargin;
	}


	function getLineHeight($fontSize = 0){
		$H = $this->HeightCell;
		if ($fontSize == 0) $fontSize = $this->FontSizePt;
		return ($fontSize*$H)/$this->k; 
	}

	private function _cellHeight(&$c){
		$maxw = $c['w0']-$this->paddingCell;
		$h = 0;
		$x = $this->paddingCell;
		$countline = 0;
		$maxline = 0;
		$c['hline'] = array();
		$c['wlinet'] = array(array(0,0));
		$c['wlines'] = array(0);
		$space = 0;
		foreach ($c['font'] as &$f){
			$this->_setFontText($f);
			$hl = $this->getLineHeight();
			if ($maxline<$hl || $x==$this->paddingCell) $maxline=$hl;
			if (!isset($f['space'])) continue;
			$space = $f['space'];
			foreach ($f['line'] as $i=>&$l){
				if ($x!=$this->paddingCell) $x+=$space;
				if (isset($l['str'])&&is_array($l['str']))
				foreach ($l['str'] as &$t){
					if (!is_array($t)) continue;
					if ($x==$this->paddingCell||$x+$t[1]<=$maxw){
						$c['wlinet'][$countline][0] += $t[1];
						$c['wlinet'][$countline][1]++;
						$x += $t[1]+(($x>$this->paddingCell)?$space:0);
					}else{//auto break line
						$h+=$maxline*FHR + $this->spacingLine;
						$c['hline'][] = $maxline*FHR + $this->spacingLine;
						$c['wlines'][$countline] = $x-$this->paddingCell;
						$c['autobr'][$countline] = 1;
						$maxline=$hl;$countline++;$x = $t[1]+$space;
						$c['wlinet'][$countline] = array($t[1],1);
					}
					$t[2] = $countline;
				}
				if ($l=='br'){
					$h+=$maxline*FHR + max($this->spacingLine,$this->spacingParagraph);
					$c['hline'][] = $maxline*FHR + $this->spacingLine;
					$c['wlines'][$countline] = $x-$this->paddingCell;
					$maxline=$hl;$countline++;$x = $this->paddingCell;
					$c['wlinet'][$countline] = array(0,0);
				}
			}
		}
		$c['wlines'][$countline] = $x-$space-$this->paddingCell;
		if ($maxline){
			$h+=$maxline;
			$c['hline'][] = $maxline*FHR;
		}
		$c['maxh'] = $h;
		return $h;
	}
	
	private function _drawCellAligned($x0,$y0,&$c){
		$maxh = $c['h0'];
		$maxw = $c['w0']-$this->paddingCell2;
		$curh = $c['maxh'];
		$x=$y=0; //Top by default
		if ($c['va']=='M') $y = ($maxh-$curh)/2; //Middle
		elseif ($c['va']=='B') $y = $maxh-$curh; //Bottom
		$curline = 0;
		$morespace = 0;
		$cl = $c['hline'][$curline];
		$this->_cellHorzAlignLine($c,$curline,$maxw,$x,$morespace);
		foreach ($c['font'] as &$f){
			$this->_setFontText($f);
			if (isset($f['color'])){
				$color = $this->HEX2RGB($f['color']);
				$this->SetTextColor($color[0],$color[1],$color[2]);
			}else unset($color);
			$hl = $this->getLineHeight();
			if (!isset($f['space'])) continue;
			$space = $f['space'];
			foreach ($f['line'] as $i=>&$l){
				if (isset($l['str'])&&is_array($l['str']))
				foreach ($l['str'] as &$t){
					if ($t[2]!=$curline){
						$y += $cl;$curline++;$cl = $c['hline'][$curline];
						$this->_cellHorzAlignLine($c,$curline,$maxw,$x,$morespace);
					}
					$this->x = $x+$x0;
					$this->y = $y+$y0+$cl;
					$this->Cell($t[1],0,$t[0]);
					$x += $t[1]+$space+$morespace;
				}
				if ($l=='br'){
					$y += $cl;$curline++;$cl = $c['hline'][$curline];
					$this->_cellHorzAlignLine($c,$curline,$maxw,$x,$morespace);
				}
			}
			if (isset($color))
				$this->SetTextColor(0);
		}
	}
	
	private function _cellHorzAlignLine(&$c,$line,$maxw,&$x,&$morespace){
		$morespace = 0;
		$x = $this->paddingCell;//Left by default
		if (!isset($c['wlines'][$line])) return ;
		if ($c['a']=='C'){//Center
			$x = ($maxw-$c['wlines'][$line])/2;
		}elseif ($c['a']=='R'){
			$x = $maxw-$c['wlines'][$line];
		}elseif ($c['a']=='J' && $c['wlinet'][$line][1]>1
			&& isset($c['autobr'][$line])){//Justify
			$morespace = ($maxw-$c['wlines'][$line])/($c['wlinet'][$line][1]-1);
		}
		if ($x < $this->paddingCell) $x = $this->paddingCell;
	}

	private function _calWidth($w){
		$p = strpos($w,'%');
		if ($p!==false){
			return intval(substr($w,0,$p)*$this->width/100);
		}else return intval($w);
	}
	
	private function _tableParser(&$html){
		$t = new TreeHTML(new HTMLParser($html), 0);
		$row	= $col	= -1;
		$table['nc'] = $table['nr'] = 0;
		$table['repeat'] = array();
		$cell   = array();
		$fontopen = false;
		$tdopen = false;
		foreach ($t->name as $i=>$element){
			if ($fontopen && $t->type[$i]==NODE_TYPE_ENDELEMENT
				&& (in_array($element,array('table','tr','td','font'))))
					$fontopen = false;
			if ($tdopen && $t->type[$i]==NODE_TYPE_ENDELEMENT
				&& (in_array($element,array('table','tr','td')))
				&& !isset($cell[$row][$col]['miw'])){
					$c = &$cell[$row][$col];
					$c['miw'] = $c['maw'] = 0;
					$tdopen = false;
			}
			if ($t->type[$i] != NODE_TYPE_ELEMENT && $t->type[$i] != NODE_TYPE_TEXT) continue;
			switch ($element){
				case 'table':
					$tdopen = 0;
					$a	= &$t->attribute[$i];
					if (isset($a['width']))		$table['w']	= $this->_calWidth($a['width']);
					if (isset($a['height']))	$table['h']	= intval($a['height']);
					if (isset($a['align']))		$table['a']	= $this->getAlign(strtolower($a['align']));
					$table['border'] = (isset($a['border']))?	$a['border']: 0;
					if (isset($a['bgcolor']))	$table['bgcolor'][-1]	= $a['bgcolor'];
					$table['nobreak'] = isset($a['nobreak']);
					break;
				case 'tr':
					$tdopen = 0;
					$row++;
					$table['nr']++;
					$col = -1;
					$a	= &$t->attribute[$i];
					if (isset($a['bgcolor']))	$table['bgcolor'][$row]	= $a['bgcolor'];
					if (isset($a['repeat']))	$table['repeat'][]		= $row;
					else{
						if (isset($a['pbr']))	$table['pbr'][$row]	= 1;
						if (isset($a['knext']))	$table['knext'][$row]	= 1;
					}
					break;
				case 'td':
					$tdopen = 1;
					$col++;while (isset($cell[$row][$col])) $col++;
					//Update number column
					if ($table['nc'] < $col+1)		$table['nc']		= $col+1;
					$cell[$row][$col] = array();
					$c = &$cell[$row][$col];
					$a	= &$t->attribute[$i];
					if (isset($a['width']))		$c['w']		= intval($a['width']);
					if (isset($a['height']))	$c['h']		= intval($a['height']);
					$c['a'] = isset($a['align'])?$this->getAlign($a['align']):'L';
					$c['va']= isset($a['valign'])?$this->getVAlign($a['valign']):'T';
					if (isset($a['border']))	$c['border']	= $a['border'];
						else					$c['border']	= $table['border'];
					if (isset($a['bgcolor']))	$c['bgcolor']	= $a['bgcolor'];
					$cs = $rs = 1;
					if (isset($a['colspan']) && $a['colspan']>1)	$cs = $c['colspan']	= intval($a['colspan']);
					if (isset($a['rowspan']) && $a['rowspan']>1)	$rs = $c['rowspan']	= intval($a['rowspan']);
					if (isset($a['size']))		$c['font'][0]['size']   	= $a['size'];
					if (isset($a['family']))	$c['font'][0]['family'] 	= $a['family'];
					if (isset($a['style'])){
						$STYLE     = explode(",", strtoupper($a['style']));
						$fontStyle = '';
						foreach($STYLE AS $style)  $fontStyle .= substr(trim($style), 0, 1);
						$c['font'][0]['style'] = $fontStyle;
					}
					if (isset($a['color']))		$c['font'][0]['color'] 		= $a['color'];
					//Chiem dung vi tri de danh cho cell span
					for ($k=$row;$k<$row+$rs;$k++) for($l=$col;$l<$col+$cs;$l++){
						if ($k-$row || $l-$col)
							$cell[$k][$l] = 0;
					}
					if (isset($a['nowrap']))	$c['nowrap']= 1;
					$fontopen = true;
					if (!isset($c['font'])) $c['font'][] = array();
					break;
				case 'Text':
					$c = &$cell[$row][$col];
					if (!$fontopen || !isset($c['font'])) $c['font'][] = array();
					$f = &$c['font'][count($c['font'])-1];
					$this->_setTextAndSize($c,$f,$this->_html2text($t->value[$i]));
					break;
				case 'font':
					$a	= &$t->attribute[$i];
					$c = &$cell[$row][$col];
					$c['font'][] = array();
					$f = &$c['font'][count($c['font'])-1];
					if (isset($a['size']))		$f['size']   	= $a['size'];
					if (isset($a['family']))	$f['family'] 	= $a['family'];
					if (isset($a['style'])){
						$STYLE     = explode(",", strtoupper($a['style']));
						$fontStyle = '';
						foreach($STYLE AS $style)  $fontStyle .= substr(trim($style), 0, 1);
						$f['style'] = $fontStyle;
					}
					if (isset($a['color']))		$f['color'] 		= $a['color'];
					break;
				case 'img':
					$a	= &$t->attribute[$i];
					if (isset($a['src'])){
						$this->_setImage($c,$a);
					}
					break;
				case 'br':
					$c = &$cell[$row][$col];
					$cn = isset($c['font'])?count($c['font'])-1:0;
					$c['font'][$cn]['line'][] = 'br';
					break;
			}
		}
		$table['cells'] = $cell;
		$table['wc']	= array_pad(array(),$table['nc'],array('miw'=>0,'maw'=>0));
		$table['hr']	= array_pad(array(),$table['nr'],0);
		return $table;
	}

	private function _setTextAndSize(&$cell,&$f, $text){
		if ($text=='') return;
		$this->_setFontText($f);
		if (!isset($f['line'][0])){
			$f['line'][0]['min'] = $f['line'][0]['max'] = 0;
		}
		$text = preg_split('/[\s]+/',$text,-1, PREG_SPLIT_NO_EMPTY);
		$l = &$f['line'][count($f['line'])-1];
		if ($l=='br'){
			$f['line'][] = array('min'=>0,'max'=>0,'str'=>array());
			$l = &$f['line'][count($f['line'])-1];
		}
		if (!isset($f['space'])) $f['space'] = $this->GetStringWidth(' ');
		$ct = count($text);
		foreach ($text as $item){
			$s = $this->GetStringWidth($item);
			if ($l['min']<$s) $l['min']=$s;
			$l['max'] += $s;
			if ($ct>1) $l['max'] += $f['space'];
			$l['str'][] =  array($item,$s);
		}
		if (isset($cell['nowrap'])) $l['min'] = $l['max'];
		if (!isset($cell['miw']) || $cell['miw']-$this->paddingCell2<$l['min'])
			$cell['miw']=$l['min']+$this->paddingCell2;
		if (!isset($cell['maw']) || $cell['maw']-$this->paddingCell2<$l['max'])
			$cell['maw']=$l['max']+$this->paddingCell2;
	}

	private function _setImage(&$c, &$a){
		$path = $a['src'];
		if (!is_file($path)){
			$this->Error('Image is not exists: '.$path);
		}else{
			list($u,$d) = $this->_getResolution($path);
			$c['img'] = $path;
			list($c['w'],$c['h'],) = getimagesize($path);
			if (isset($a['width'])) $c['w'] = $a['width'];
			if (isset($a['height'])) $c['h'] = $a['height'];
			$scale = 1;
			if ($u==1) $scale = 25.4/$d;
			elseif ($u==2) $scale = 10/$d;
			$c['w'] = intval($c['w']*$scale);
			$c['h'] = intval($c['h']*$scale);
		}
	}
	
	private function _getResolution($path){
		$pos=strrpos($path,'.');
		if(!$pos)
			$this->Error('Image file has no extension and no type was specified: '.$path);
		$type=substr($path,$pos+1);
		$type=strtolower($type);
		if($type=='jpeg')
			$type='jpg';
		if ($type!='jpg')
			$this->Error('Unsupported image type: '.$path);
		$f = fopen($path,'r');
		fseek($f,13,SEEK_SET);
		$info = fread($f,3);
		 fclose($f);
		 $iUnit = ord($info[0]);
		 $iX = ord($info[1]) * 256 + ord($info[2]);
		 return array($iUnit, $iX);
	}
	
	private function _html2text($text){
		$text = str_replace('&nbsp;',' ',$text);
		$text = str_replace('&lt;','<',$text);
		return $text;
	}

/**
 * table	Array of (w, h, bc, nr, wc, hr, cells)
 * w		Width of table
 * h		Height of table
 * bc		Number column
 * nr		Number row
 * hr		List of height of each row
 * wc		List of width of each column
 * cells	List of cells of each rows, cells[i][j] is a cell in table
 */
	private function _tableColumnWidth(&$table){
		$cs = &$table['cells'];
		$nc = $table['nc'];
		$nr = $table['nr'];
		$listspan = array();
		//Xac dinh do rong cua cac cell va cac cot tuong ung
		for ($j=0;$j<$nc;$j++){
			$wc = &$table['wc'][$j];
			for ($i=0;$i<$nr;$i++){
				if (isset($cs[$i][$j]['miw'])){
					$c   = &$cs[$i][$j];
					if (isset($c['nowrap'])) $c['miw'] = $c['maw'];
					if (isset($c['w'])){
						if ($c['miw']<$c['w']) $c['miw'] = $c['w'];
						elseif ($c['miw']>$c['w']) $c['w'] = $c['miw']+$this->paddingCell2;
						if (!isset($wc['w'])) $wc['w'] = 1;
					}
					if ($c['maw']<$c['miw']) $c['maw'] = $c['miw'];
					if (!isset($c['colspan'])){
						if ($wc['miw'] < $c['miw']) $wc['miw']=$c['miw'];
						if ($wc['maw'] < $c['maw']) $wc['maw']=$c['maw'];
						if (isset($wc['w']) && $wc['w'] < $wc['miw'])
							$wc['w']=$wc['miw'];
					}else $listspan[] = array($i,$j);
				}
			}
		}
	
		$wc = &$table['wc'];
		foreach ($listspan as $span){
			list($i,$j) = $span;
			$c = &$cs[$i][$j];
			$lc = $j + $c['colspan'];
			if ($lc > $nc) $lc = $nc;
	
			$wis = $wisa = 0;
			$was = $wasa = 0;
			$list = array();
			for($k=$j;$k<$lc;$k++){
				$wis += $wc[$k]['miw'];
				$was += $wc[$k]['maw'];
				if (!isset($c['w'])){
					$list[] = $k;
					$wisa += $wc[$k]['miw'];
					$wasa += $wc[$k]['maw'];
				}
			}
			if ($c['miw'] > $wis){
				if (!$wis){//Cac cot chua co kich thuoc => chia deu
					for($k=$j;$k<$lc;$k++) $wc[$k]['miw'] = $c['miw']/$c['colspan'];
				}elseif (!count($list)){//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
					$wi = $c['miw'] - $wis;
					for($k=$j;$k<$lc;$k++)
						$wc[$k]['miw'] += ($wc[$k]['miw']/$wis)*$wi;
				}else{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
					$wi = $c['miw'] - $wis;
					foreach ($list as $_z2=>$k)
						$wc[$k]['miw'] += ($wc[$k]['miw']/$wisa)*$wi;
				}
			}
			if ($c['maw'] > $was){
				if (!$wis){//Cac cot chua co kich thuoc => chia deu
					for($k=$j;$k<$lc;$k++) $wc[$k]['maw'] = $c['maw']/$c['colspan'];
				}elseif (!count($list)){//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
					$wi = $c['maw'] - $was;
					for($k=$j;$k<$lc;$k++)
						$wc[$k]['maw'] += ($wc[$k]['maw']/$was)*$wi;
				}else{//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
					$wi = $c['maw'] - $was;
					foreach ($list as $_z2=>$k)
						$wc[$k]['maw'] += ($wc[$k]['maw']/$wasa)*$wi;
				}
			}
		}
	}

	private function _tableWidth(&$table){
		$wc = &$table['wc'];
		$nc = $table['nc'];
		$a = 0;
		for ($i=0;$i<$nc;$i++){
			$a += isset($wc[$i]['w']) ? $wc[$i]['miw'] : $wc[$i]['maw'];
		}
		if ($a > $this->width) $table['w'] = $this->width;
	
		if (isset($table['w'])){
			$wis = $wisa = 0;
			$list = array();
			for ($i=0;$i<$nc;$i++){
				$wis += $wc[$i]['miw'];
				if (!isset($wc[$i]['w'])){ $list[] = $i;$wisa += $wc[$i]['miw'];}
			}
			if ($table['w'] > $wis){
				if (!count($list)){
					//Khong co cot nao co kich thuoc auto => chia deu phan du cho tat ca
					$wi = ($table['w'] - $wis)/$nc;
					for($k=0;$k<$nc;$k++)
						$wc[$k]['miw'] += $wi;
				}else{
					//Co mot so cot co kich thuoc auto => chia deu phan du cho cac cot auto
					$wi = ($table['w'] - $wis)/count($list);
					foreach ($list as $k)
						$wc[$k]['miw'] += $wi;
				}
			}
			for ($i=0;$i<$nc;$i++){
				$a = $wc[$i]['miw'];
				unset($wc[$i]);
				$wc[$i] = $a;
			}
		}else{
			$table['w'] = $a;
			for ($i=0;$i<$nc;$i++){
				$a = isset($wc[$i]['w']) ? $wc[$i]['miw'] : $wc[$i]['maw'];
				unset($wc[$i]);
				$wc[$i] = $a;
			}
		}
		$table['w'] = array_sum($wc);
	}

	private function _tableHeight(&$table){
		$cs = &$table['cells'];
		$nc = $table['nc'];
		$nr = $table['nr'];
		$listspan = array();
		for ($i=0;$i<$nr;$i++){
			$hr = &$table['hr'][$i];
			for ($j=0;$j<$nc;$j++){
				if (isset($cs[$i][$j]['miw'])){
					$c = &$cs[$i][$j];
					$this->_tableGetWCell($table, $i,$j); //create $c['x0'], $c['w0']
	
					$ch = $this->_cellHeight($c);
					$c['ch'] = $ch;
	
					if (isset($c['h']) && $c['h'] > $ch) $ch = $c['h'];
	
					if (isset($c['rowspan'])) $listspan[] = array($i,$j);
					elseif ($hr < $ch) $hr = $ch;
					$c['mih'] = $ch;
				}
			}
		}
		$hr = &$table['hr'];
		foreach ($listspan as $span){
			list($i,$j) = $span;
			$c = &$cs[$i][$j];
			$lr = $i + $c['rowspan'];
			if ($lr > $nr) $lr = $nr;
			$hs = $hsa = 0;
			$list = array();
			for($k=$i;$k<$lr;$k++){
				$hs += $hr[$k];
				if (!isset($c['h'])){
					$list[] = $k;
					$hsa += $hr[$k];
				}
			}
			if ($c['mih'] > $hs){
				if (!$hs){//Cac dong chua co kich thuoc => chia deu
					for($k=$i;$k<$lr;$k++) $hr[$k] = $c['mih']/$c['rowspan'];
				}elseif (!count($list)){
					//Khong co dong nao co kich thuoc auto => chia deu phan du cho tat ca
					$hi = $c['mih'] - $hs;
					for($k=$i;$k<$lr;$k++)
						$hr[$k] += ($hr[$k]/$hs)*$hi;
				}else{
					//Co mot so dong co kich thuoc auto => chia deu phan du cho cac dong auto
					$hi = $c['mih'] - $hsa;
					foreach ($list as $k)
						$hr[$k] += ($hr[$k]/$hsa)*$hi;
				}
			}
		}
		$table['repeatH'] = 0;
		if (count($table['repeat'])){
			foreach ($table['repeat'] as $i) $table['repeatH'] += $hr[$i];
		}else $table['repeat'] = 0;
		$tth = 0;
		foreach ($hr as $v) $tth+=$v;
		$table['tth'] = $tth;
	}

	private function _tableGetWCell(&$table, $i,$j){
		$c = &$table['cells'][$i][$j];
		if ($c){
			if (isset($c['x0'])) return array($c['x0'], $c['w0']);
			$x = 0;
			$wc = &$table['wc'];
			for ($k=0;$k<$j;$k++) $x += $wc[$k];
			$w = $wc[$j];
			if (isset($c['colspan'])){
				for ($k=$j+$c['colspan']-1;$k>$j;$k--)
					$w += @$wc[$k];
			}
			$c['x0'] = $x;
			$c['w0'] = $w;
			return array($x, $w);
		}
		return array(0,0);
	}

	private function _tableGetHCell(&$table, $i,$j){
		$c = &$table['cells'][$i][$j];
		if ($c){
			if (isset($c['h0'])) return $c['h0'];
			$hr = &$table['hr'];
			$h = $hr[$i];
			if (isset($c['rowspan'])){
				for ($k=$i+$c['rowspan']-1;$k>$i;$k--)
					$h += $hr[$k];
			}
			$c['h0'] = $h;
			return $h;
		}
		return 0;
	}

	private function _tableRect($x, $y, $w, $h, $type=1){
		if (strlen($type)==4)
		{
			$x2 = $x + $w; $y2 = $y + $h;
			if (intval($type[0])) $this->Line($x , $y , $x2, $y );
			if (intval($type[1])) $this->Line($x2, $y , $x2, $y2);
			if (intval($type[2])) $this->Line($x , $y2, $x2, $y2);
			if (intval($type[3])) $this->Line($x , $y , $x , $y2);
		}
		elseif ((int)$type===1)
			$this->Rect($x, $y, $w, $h);
		elseif ((int)$type>1 && (int)$type<11)
		{
			$width = $this->LineWidth;
			$this->SetLineWidth($type * $this->LineWidth);
			$this->Rect($x, $y, $w, $h);
			$this->SetLineWidth($width);
		}
	}

	private function _tableDrawBorder(&$table){
		//When fill a cell, it overwrite the border of prevous cell, then I have to draw border at the end
		foreach ($table['listborder'] as $c){
			list($x,$y,$w,$h,$s) = $c;
			$this->_tableRect($x,$y,$w,$h,$s);
		}
	
		$table['listborder'] = array();
	}
	
	private function _getRowHeight(&$table,$i){
		$maxh = 0;
		for ($j=0;$j<$table['nc'];$j++){
			$h = $this->_tableGetHCell($table, $i, $j);
			if ($maxh < $h) $maxh = $h;
		}
		return $maxh;
	}
	
	private function _checkLimitHeight(&$table,$maxh){
		if ($maxh+$table['repeatH'] > $this->height){
			$msg = 'Height of this row is greater than page height!';
			$this->SetFillColor(255,0,0);
			$h=$this->bottom-$table['lasty'];
			$this->Rect($this->x, $this->y=$table['lasty'], $table['w'], $h, 'F');
			$this->MultiCell($table['w'],$h,$msg);
			$table['lasty'] += $h;
			return 1;
		}
		return 0;
	}
	
	private function _tableWriteRow(&$table,$i,$x0){
		$maxh = $this->_getRowHeight($table, $i);
		if ($table['multipage']){
			$newpage = 0;
			if ($table['lasty']+$maxh>$this->bottom){
				if ($this->_checkLimitHeight($table, $maxh)) return;
				$newpage = 1;
			}elseif (isset($table['pbr'][$i])){
				$newpage = 1;
				unset($table['pbr'][$i]);
			}elseif (isset($table['knext'][$i])&&$i<$table['nr']-1){
				$mrowh = $maxh;
				for($j=$i+1;$j<$table['nr'];$j++){
					$mrowh += $this->_getRowHeight($table, $j);
					if (!isset($table['knext'][$j])) break;
					unset($table['knext'][$j]);
				}
				if ($this->_checkLimitHeight($table, $mrowh)) return;
				$newpage = $table['lasty']+$mrowh>$this->bottom;
			}
			if ($newpage){
				$this->_tableDrawBorder($table);
				$this->AddPage($this->CurOrientation);
		
				$table['lasty'] = $this->y;
				if ($table['repeat']){
					foreach ($table['repeat'] as $r){
						if ($r==$i) continue;
						$this->_tableWriteRow($table,$r,$x0);
					}
				}
			}
		}
		$y = $table['lasty'];
		for ($j=0;$j<$table['nc'];$j++){
			if (isset($table['cells'][$i][$j]['miw'])){
				$c = &$table['cells'][$i][$j];
				list($x,$w) = $this->_tableGetWCell($table, $i, $j);
				$h = $this->_tableGetHCell($table, $i, $j);
				$x += $x0;
				
				//Fill
				$fill = isset($c['bgcolor']) ? $c['bgcolor']
					: (isset($table['bgcolor'][$i]) ? $table['bgcolor'][$i]
					: (isset($table['bgcolor'][-1]) ? $table['bgcolor'][-1] : 0));
				if ($fill){
					$color = $this->HEX2RGB($fill);
					
					$this->SetFillColor($color[0],$color[1],$color[2]);
					$this->Rect($x, $y, $w, $h, 'F');
				};
				//Content
				if (isset($c['img'])){
					$this->Image($c['img'],$x,$y,$c['w'],$c['h']);
				}else{
					$this->_drawCellAligned($x,$y,$c);
				}
				//Border
				if (isset($c['border'])){
					$table['listborder'][] = array($x,$y,$w,$h,$c['border']);
				}elseif (isset($table['border']) && $table['border'])
					$table['listborder'][] = array($x,$y,$w,$h,$table['border']);
			}
		}
		$table['lasty'] += $table['hr'][$i];
		$this->y = $table['lasty'];
	}
	
	function SetFont($family, $style='', $size=0, $default=false){
		parent::SetFont($family, $style, $size);
		if ($default||$this->isNotYetSetFont){
			$this->defaultFontFamily = $family;
			$this->defaultFontSize = $size;
			$this->defaultFontStyle = $style;
			$this->isNotYetSetFont = false;
		}
	}
	private function _setFontText(&$f){
		if (isset($f['size']) && ($f['size'] >0)){
			$fontSize   = $f['size'];
		}else $fontSize   = $this->defaultFontSize;
		if (isset($f['family'])){
			$fontFamily = $f['family'];
		}else $fontFamily = $this->defaultFontFamily;
		if (isset($f['style']))
			$fontStyle  = $f['style'];
		else $fontStyle = $this->defaultFontStyle;
		$this->SetFont($fontFamily, $fontStyle, $fontSize);
		return $fontSize;
	}
	private function _tableWrite(&$table){
		if ($this->CurOrientation == 'P' && $table['w']>$this->width+5){
			$this->AddPage('L');
		}
		if ($this->x==null)$this->x = $this->lMargin;
		if ($this->y==null)$this->y = $this->tMargin;
		$x0 = $this->x;
		$y0 = $this->y;
		if (isset($table['a'])){
			if ($table['a']=='C'){
				$x0 += (($this->right-$x0) - $table['w'])/2;
			}elseif ($table['a']=='R'){
				$x0 = $this->right - $table['w'];
			}
		}
		if (isset($table['nobreak'])&&$table['nobreak']
			&& $table['tth']+$y0>$this->bottom && $table['multipage']){
			$this->AddPage($this->CurOrientation);
			$table['lasty'] = $this->y;
		}else
			$table['lasty'] = $y0;
		
		$table['listborder'] = array();
		for ($i=0;$i<$table['nr'];$i++) $this->_tableWriteRow($table, $i, $x0);
		$this->_tableDrawBorder($table);
		$this->x = $x0;
	}

	function htmltable(&$html,$HeightCell=0,$multipage=1){
		$a = $this->AutoPageBreak;
		$this->SetAutoPageBreak(0,$this->bMargin);
		$HTML = explode("<table", utf8_decode($html));
		if ($HeightCell == 0) $HeightCell = 2;
		$this->HeightCell = $HeightCell;
		$oldMargin = $this->cMargin;
		$this->cMargin = 0;
		$x = $this->x;
		foreach ($HTML as $i=>$table){	
			$this->x = $x;
			if (strlen($table)<6) continue;
			$table = '<table' . $table;
			$table = $this->_tableParser($table);
			$table['multipage'] = $multipage;
			$this->_tableColumnWidth($table);
			$this->_tableWidth($table);
			$this->_tableHeight($table);
			$this->_tableWrite($table);
		}
		$this->cMargin = $oldMargin;
		$this->SetAutoPageBreak($a,$this->bMargin);
	}
	
	function _putinfo(){
		$this->_out('/Producer '.$this->_textstring('PDFTable '.
			PDFTABLE_VERSION.' based on FPDF '.FPDF_VERSION));
		if(!empty($this->title))
			$this->_out('/Title '.$this->_textstring($this->title));
		if(!empty($this->subject))
			$this->_out('/Subject '.$this->_textstring($this->subject));
		if(!empty($this->author))
			$this->_out('/Author '.$this->_textstring($this->author));
		if(!empty($this->keywords))
			$this->_out('/Keywords '.$this->_textstring($this->keywords));
		if(!empty($this->creator))
			$this->_out('/Creator '.$this->_textstring($this->creator));
		$this->_out('/CreationDate '.$this->_textstring('D:'.@date('YmdHis')));
	}
	
	private function getAlign($v){
		/* global $PDF_ALIGN;  this is not working on codeigniter */
		$PDF_ALIGN  = array('left'=>'L','center'=>'C','right'=>'R','justify'=>'J');
		$v = strtolower($v);
		return isset($PDF_ALIGN[$v])?$PDF_ALIGN[$v]:'L';
	}
	private function getVAlign($v){
		/* global $PDF_VALIGN; this is not working on codeigniter */
		$PDF_VALIGN = array('top'=>'T','middle'=>'M','bottom'=>'B');
		$v = strtolower($v);
		return isset($PDF_VALIGN[$v])?$PDF_VALIGN[$v]:'T';
	}
//tambahan diambil dari color.inc.hp
	function HEX2RGB($c){
		if (strlen($c)!=7) return 0;
		
		$r[] = hexdec($c[1].$c[2]);
		$r[] = hexdec($c[3].$c[4]);
		$r[] = hexdec($c[5].$c[6]);
				
		return $r;
	}
	
// tambahan barcode
// $x = posisi kolom kekanan
// $y = posisi baris atas bawah
// $code = nilai yang akan digenerate
// $w = ketebalan batang barcode (width)
// $h = panjang batang
// $wide = panjang keseluruhan barcode
// $label = include text nilai atau tidak

	function Code39($x, $y, $code, $w=0.4, $h=10, $wide=true, $label=true, $ext=true, $cks=false) {
		if($label) {
			$this->Text($x, $y+$h+4, $code);
		}
		
		if($ext) {
			//Extended encoding
			$code = $this->encode_code39_ext($code);
		}
		else {
			//Convert to upper case
			$code = strtoupper($code);
			//Check validity
			if(!preg_match('|^[0-9A-Z. $/+%-]*$|', $code))
				$this->Error('Invalid barcode value: '.$code);
		}
	
		//Compute checksum
		if ($cks)
			$code .= $this->checksum_code39($code);
	
		//Add start and stop characters
		$code = '*'.$code.'*';
	
		//Conversion tables
		$narrow_encoding = array (
			'0' => '101001101101', '1' => '110100101011', '2' => '101100101011',
			'3' => '110110010101', '4' => '101001101011', '5' => '110100110101',
			'6' => '101100110101', '7' => '101001011011', '8' => '110100101101',
			'9' => '101100101101', 'A' => '110101001011', 'B' => '101101001011',
			'C' => '110110100101', 'D' => '101011001011', 'E' => '110101100101',
			'F' => '101101100101', 'G' => '101010011011', 'H' => '110101001101',
			'I' => '101101001101', 'J' => '101011001101', 'K' => '110101010011',
			'L' => '101101010011', 'M' => '110110101001', 'N' => '101011010011',
			'O' => '110101101001', 'P' => '101101101001', 'Q' => '101010110011',
			'R' => '110101011001', 'S' => '101101011001', 'T' => '101011011001',
			'U' => '110010101011', 'V' => '100110101011', 'W' => '110011010101',
			'X' => '100101101011', 'Y' => '110010110101', 'Z' => '100110110101',
			'-' => '100101011011', '.' => '110010101101', ' ' => '100110101101',
			'*' => '100101101101', '$' => '100100100101', '/' => '100100101001',
			'+' => '100101001001', '%' => '101001001001' );
	
		$wide_encoding = array (
			'0' => '101000111011101', '1' => '111010001010111', '2' => '101110001010111',
			'3' => '111011100010101', '4' => '101000111010111', '5' => '111010001110101',
			'6' => '101110001110101', '7' => '101000101110111', '8' => '111010001011101',
			'9' => '101110001011101', 'A' => '111010100010111', 'B' => '101110100010111',
			'C' => '111011101000101', 'D' => '101011100010111', 'E' => '111010111000101',
			'F' => '101110111000101', 'G' => '101010001110111', 'H' => '111010100011101',
			'I' => '101110100011101', 'J' => '101011100011101', 'K' => '111010101000111',
			'L' => '101110101000111', 'M' => '111011101010001', 'N' => '101011101000111',
			'O' => '111010111010001', 'P' => '101110111010001', 'Q' => '101010111000111',
			'R' => '111010101110001', 'S' => '101110101110001', 'T' => '101011101110001',
			'U' => '111000101010111', 'V' => '100011101010111', 'W' => '111000111010101',
			'X' => '100010111010111', 'Y' => '111000101110101', 'Z' => '100011101110101',
			'-' => '100010101110111', '.' => '111000101011101', ' ' => '100011101011101',
			'*' => '100010111011101', '$' => '100010001000101', '/' => '100010001010001',
			'+' => '100010100010001', '%' => '101000100010001');
	
		$encoding = $wide ? $wide_encoding : $narrow_encoding;
	
		//Inter-character spacing
		$gap = ($w > 0.29) ? '00' : '0';
	
		//Convert to bars
		$encode = '';
		for ($i = 0; $i< strlen($code); $i++)
			$encode .= $encoding[$code[$i]].$gap;
	
		//Draw bars
		$this->draw_code39($encode, $x, $y, $w, $h);
	}

	function checksum_code39($code) {
	
		//Compute the modulo 43 checksum
	
		$chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
								'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
								'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
								'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%');
		$sum = 0;
		for ($i=0 ; $i<strlen($code); $i++) {
			$a = array_keys($chars, $code[$i]);
			$sum += $a[0];
		}
		$r = $sum % 43;
		return $chars[$r];
	}

	function encode_code39_ext($code) {
	
		//Encode characters in extended mode
	
		$encode = array(
			chr(0) => '%U', chr(1) => '$A', chr(2) => '$B', chr(3) => '$C',
			chr(4) => '$D', chr(5) => '$E', chr(6) => '$F', chr(7) => '$G',
			chr(8) => '$H', chr(9) => '$I', chr(10) => '$J', chr(11) => '�K',
			chr(12) => '$L', chr(13) => '$M', chr(14) => '$N', chr(15) => '$O',
			chr(16) => '$P', chr(17) => '$Q', chr(18) => '$R', chr(19) => '$S',
			chr(20) => '$T', chr(21) => '$U', chr(22) => '$V', chr(23) => '$W',
			chr(24) => '$X', chr(25) => '$Y', chr(26) => '$Z', chr(27) => '%A',
			chr(28) => '%B', chr(29) => '%C', chr(30) => '%D', chr(31) => '%E',
			chr(32) => ' ', chr(33) => '/A', chr(34) => '/B', chr(35) => '/C',
			chr(36) => '/D', chr(37) => '/E', chr(38) => '/F', chr(39) => '/G',
			chr(40) => '/H', chr(41) => '/I', chr(42) => '/J', chr(43) => '/K',
			chr(44) => '/L', chr(45) => '-', chr(46) => '.', chr(47) => '/O',
			chr(48) => '0', chr(49) => '1', chr(50) => '2', chr(51) => '3',
			chr(52) => '4', chr(53) => '5', chr(54) => '6', chr(55) => '7',
			chr(56) => '8', chr(57) => '9', chr(58) => '/Z', chr(59) => '%F',
			chr(60) => '%G', chr(61) => '%H', chr(62) => '%I', chr(63) => '%J',
			chr(64) => '%V', chr(65) => 'A', chr(66) => 'B', chr(67) => 'C',
			chr(68) => 'D', chr(69) => 'E', chr(70) => 'F', chr(71) => 'G',
			chr(72) => 'H', chr(73) => 'I', chr(74) => 'J', chr(75) => 'K',
			chr(76) => 'L', chr(77) => 'M', chr(78) => 'N', chr(79) => 'O',
			chr(80) => 'P', chr(81) => 'Q', chr(82) => 'R', chr(83) => 'S',
			chr(84) => 'T', chr(85) => 'U', chr(86) => 'V', chr(87) => 'W',
			chr(88) => 'X', chr(89) => 'Y', chr(90) => 'Z', chr(91) => '%K',
			chr(92) => '%L', chr(93) => '%M', chr(94) => '%N', chr(95) => '%O',
			chr(96) => '%W', chr(97) => '+A', chr(98) => '+B', chr(99) => '+C',
			chr(100) => '+D', chr(101) => '+E', chr(102) => '+F', chr(103) => '+G',
			chr(104) => '+H', chr(105) => '+I', chr(106) => '+J', chr(107) => '+K',
			chr(108) => '+L', chr(109) => '+M', chr(110) => '+N', chr(111) => '+O',
			chr(112) => '+P', chr(113) => '+Q', chr(114) => '+R', chr(115) => '+S',
			chr(116) => '+T', chr(117) => '+U', chr(118) => '+V', chr(119) => '+W',
			chr(120) => '+X', chr(121) => '+Y', chr(122) => '+Z', chr(123) => '%P',
			chr(124) => '%Q', chr(125) => '%R', chr(126) => '%S', chr(127) => '%T');
	
		$code_ext = '';
		for ($i = 0 ; $i<strlen($code); $i++) {
			if (ord($code[$i]) > 127)
				$this->Error('Invalid character: '.$code[$i]);
			$code_ext .= $encode[$code[$i]];
		}
		return $code_ext;
	}

	function draw_code39($code, $x, $y, $w, $h) {
	
		//Draw bars
	
		for($i=0; $i<strlen($code); $i++) {
			if($code[$i] == '1')
				$this->Rect($x+$i*$w, $y, $w, $h, 'F');
		}
	}

		
}//PDFTable

?>