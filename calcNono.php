<?php
foreach (glob("classes/*.php") as $filename) {
    include $filename;
}

$data='';
if(isset($_POST['rows'])&&isset($_POST['columns'])){
	if($_POST['rows']!=''&&$_POST['columns']!=''){
		$data="{ \"rows\":[" . htmlspecialchars($_POST['rows']) . "],
		 \"columns\":[" . htmlspecialchars($_POST['columns']) . "]}";
	}
}else{
	$data=file_get_contents("nonograms/input15x15.json");
}

if($data!=""){
	$tmp=json_decode($data,true);
	if(!is_null($tmp)){
		$row=$tmp["rows"];
		$col=$tmp["columns"];
		$valid=true;
		for($i=0;$i<count($row);$i++){
			for($j=0;$j<count($row[$i]);$j++){
				if(is_int($row[$i][$j])&&$row[$i][$j]<=0){
					$valid=false;
					echo "<span style='color: #ff0000'> Введенны неверные значения строк!</span>";
				}
			}
		}
		if($valid){
			for($i=0;$i<count($col);$i++){
				for($j=0;$j<count($col);$j++){
					if(is_int($col[$i][$j])&&$col[$i][$j]<=0){
						$valid=false;
						echo "<span style='color: #ff0000'> Введенны неверные значения столбцов!</span>";
					}
				}
			}
		}
		if($valid){
			$nono=new Nono($row,$col);
			$nono->solve();
			$nono->printMap();
		}
	}
	else{
		echo "<span style='color: #ff0000'> Введенны некорректные значения!</span>";
	}
}else{
	echo "<span style='color: #ff0000'> Введите значения!</span>";
}
		
