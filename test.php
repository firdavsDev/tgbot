<?php

$bot_token = "1457309663:AAFPdb1fFUqiKmm6Ti58oHLaSO3mDX3qvq0";//token

$website = "https://api.telegram.org/bot";
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
$message = $update["message"]; 
$from = $message["from"];
$username = $from["username"];
$chat_id = $message["chat"]["id"];
$text = $message["text"];

$admin_id='937152038';
$url="";
$postfields=[];


function sendMessage($url,$postfields){


if (!$curld = curl_init()) {
exit;
}

curl_setopt($curld, CURLOPT_POST, true);
curl_setopt($curld, CURLOPT_POSTFIELDS, $postfields);
curl_setopt($curld, CURLOPT_URL,$url);
curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);

$output = curl_exec($curld);

curl_close ($curld);
}





if($text=="/start")
{
	$reply = "
Assalomu aleykum xush kelibsiz.
.....
1️⃣ Test yaratish uchun
/add*fan nomi*test kalitlari 
ko`rinishida yuboring.

Misol: /add*fan nomi*abcbabcdbcd...


2️⃣ Test javoblarini yuborish uchun 
test kodi*Ism Familiya*abcdbabcdb... 
ko`rinishida yuboring.

Misol: 
kod100*Ga'ffarov shoxrux*aaabbbccc...";

    $url = $website.$bot_token."/sendMessage";
	$metod = "/sendMessage";

	$postfields = array(
		'chat_id' => "$chat_id",
		'text' => "$reply",
		'parse_mode'=>"html");

	print_r($postfields);
	
	sendMessage($url,$postfields);
	}else

	if(explode('*',$text)[0]=="/add")
	{
	    $fan=trim(explode('*',$text)[1]);
		$javob=trim(explode('*',$text)[2]);
		$soni=strlen($javob);
		
		require('config.php');

		$sql = "Insert into testlar(fan_nomi,test_javob,avtor_id,testlar_soni,status,created_date_time) values('$fan','$javob','$chat_id',$soni,'open','".date("Y-m-d H:i:s", strtotime('+5 hours'))."')";

		if ($conn->query($sql) === TRUE) 
		{
    		$last_id = $conn->insert_id;
    		$reply="✅Test bazaga qo`shildi.

Test kodi: kod".$last_id."
Savollar soni: ".$soni." ta

Testda qatnashuvchilar quyidagi ko`rinishda javob yuborishlari mumkin:

kod".$last_id."*Ism Familiya*".$javob."

Testni yakunlash va natijalarni ko`rish uchun 
/stop_kod".$last_id." ni bosing.

Testning joriy holatini ko`rish uchun 
/natija_kod".$last_id." ni bosing.";
		} 
		else 
		{
    		$reply="❌Xatolik.\r\n\n". $conn->error;
		}

		$conn->close();

		$url = $website.$bot_token."/sendMessage";

		$postfields = array(
			'chat_id' => "$chat_id",
			'text' => "$reply");

		print_r($postfields);
		
		sendMessage($url,$postfields);
	}
	else
		if(substr(explode('*',$text)[0],0,3)=="kod")
		{
			$url = $website.$bot_token."/sendMessage";
          
			$test_id=substr($text,3,strpos($text,"*",3)-3);
			 $text = str_replace("'","`",$text);
			$fio=trim(explode('*',$text)[1]);
			$user_javob=trim(strtolower(explode('*',$text)[2]));
			
			require('config.php');

			$sql = "SELECT * FROM users where chatId=".$chat_id." and test_id=".$test_id;
			$result = $conn->query($sql);
			$oldingi_natija=0;
			$jami=0;
			if ($result->num_rows > 0) 
			{
			    while($row = $result->fetch_assoc()) 
    			{
        			$oldingi_natija = $row["count_corrects"];
    			}
    			
				$reply="❗️❗️❗️
Siz oldinroq bu testga javob yuborgansiz.

Bitta testga faqat bir marta javob yuborish mumkin!

Sizning oldingi natijangiz: ".$oldingi_natija." ta";
				$conn->close();
   				goto a;
			}

			$sql = "SELECT * FROM testlar where id=".$test_id;
			$result = $conn->query($sql);
			$aa="0";
			$count=0;
			$state="";
			$avtorId=0;
			
			if ($result->num_rows > 0) 
			{
    			while($row = $result->fetch_assoc()) 
    			{
        			$aa = strtolower($row["test_javob"]);
        			$count=$row["testlar_soni"];
        			$state=$row["status"];
        			$avtorId=$row["avtor_id"];
    			}
			}
			else
			{
   				$reply="Xatolik!\r\nTest bazadan topilmadi.\r\nTest kodini noto`g`ri yuborgan bo`lishingiz mumkin, iltimos tekshirib qaytadan yuboring.";
   				$conn->close();
   				goto a;
			}


			if(strlen($user_javob)!=$count)
			{
    			$reply=explode('*',$text)[0]." kodli testda savollar soni ".$count." ta.\r\n❌Siz esa ".strlen($user_javob)." ta javob yozdingiz!";
    			$conn->close();
   				goto a;
			}
			else
				if($state=="lock")
				{
					$reply="☹️☹️☹️
Afsuski siz javob yuborishga kechikdingiz!!!
Test yakunlangan.

Keyingi testda chaqqonroq bo`ling hurmatli foydalanuvchi...";

				$conn->close();
				goto a;

				}
				else
				{
				    $incorrects = [];
					$k=0;
					for($i=0;$i<strlen($aa);$i++)
					{
    					if($aa[$i]==$user_javob[$i])
    					{
        					$k=$k+1;
    					}
    					else
    					{
    					    array_push($incorrects, ($i+1));
    					}
					}


					$sql = "Insert into users(fio,username,chatId,test_id,user_javob,count_corrects,foiz,incorrects,sana_vaqt) values('$fio','username','$chat_id',$test_id,'$user_javob',$k,$k*100/$count,'".json_encode($incorrects)."','".date("Y-m-d H:i:s", strtotime('+5 hours'))."')";

					if ($conn->query($sql) === TRUE) 
					{
					    $jamixolat = $count-$k;
    					$reply="👤 Foydalanuvchi: <a href='tg://user?id=".$chat_id."'>".$fio."</a>
📖 Test kodi: <b>".explode('*',$text)[0]."</b>
✏️ Jami savollar soni: ".$count." ta
✅ To'g'ri javoblar soni: ".$k." ta
🔣 Foiz : ".$k*100/$count." %

❓ Noto`g`ri javoblaringiz: $jamixolat

🕐 Sana, vaqt: ".date("Y-m-d H:i:s", strtotime('+5 hours'));

						$otchyot="<a href='tg://user?id=".$chat_id."'>".$fio."</a> <b>".explode('*',$text)[0]."</b> testning javoblarini yubordi.";
						$postfields1 = array(
							'chat_id' => "$avtorId",
							'text' => "$otchyot",
							'parse_mode'=>"html");

							print_r($postfields1);

						sendMessage($url,$postfields1);
					} 
					else 
					{
    					$reply="❌Xatolik.\r\n\n". $conn->error;
					}

					$conn->close();
				}

			a:$postfields = array(
				'chat_id' => "$chat_id",
				'text' => "$reply",
				'parse_mode'=>"html");

			print_r($postfields);
			
			sendMessage($url,$postfields);
		}
		else
				if(substr($text,0,5)=="/stop")
				{
                        $url = $website.$bot_token."/sendMessage";
					    $test_id=substr($text,9);
					    $fan_nomi="";

                        require('config.php');
                        
                        $sql = "SELECT * FROM testlar where id=".$test_id;
					    $result = $conn->query($sql);
					    if ($result->num_rows > 0) 
					    {
    					   while($row = $result->fetch_assoc()) 
    					   {
        					   $fan_nomi=$row["fan_nomi"];
    					   }
    					        
					    }
					    
					    
					$sql = "SELECT * FROM testlar where id=".$test_id;
					$result = $conn->query($sql);
					$avtorId=0;
					$test_javobi="";
					$testkodi="";
					$testsoni=0;
					if ($result->num_rows > 0) 
					{
    					while($row = $result->fetch_assoc()) 
    					{
        					$avtorId = $row["avtor_id"];
        					$test_javobi=$row["test_javob"];
        					$testkodi="kod".$row["id"];
        					$testsoni=$row["testlar_soni"];
    					}
    					
    					$reply="⛔️Test yakunlandi.\r\n\nTest kodi: ".$testkodi."\r\n\n✅Natijalar:\r\n\n";
                        $reply="⛔️Test yakunlandi.

Fan: ".$fan_nomi."
Test kodi: kod".$test_id."
Savollar soni: ".$testsoni." ta

✅ Natijalar:

";  
                        $sql = "SELECT * FROM users where test_id=".$test_id." order by count_corrects desc, sana_vaqt";
					    $result = $conn->query($sql);
					    $x=1;
					    if ($result->num_rows > 0) 
					    {
    					   while($row = $result->fetch_assoc()) 
    					   {
        					   $reply=$reply.$x." <a href='tg://user?id=".$row["chatId"]."'>".$row["fio"]."</a> - ".$row["count_corrects"]." ta\r\n";
        					            $x=$x+1;
    					   }
    					   
    					   $reply=$reply."\r\nTo`g`ri javoblar:\r\n".$test_javobi;
    					        
					    }
					    else
					    {
					       $reply="❌❌❌
Bu testga hech kim javob yubormagan.
Kamida bir kishi javob yuborgandan so`ng testni yakunlash mumkin.";
                            goto z;
					    }
					            
    					if($chat_id==$avtorId)
    					{
    					    $sql = "UPDATE testlar SET status='lock' WHERE id=".$test_id;

                            if ($conn->query($sql) === false) 
                            {
                                $reply="❌❌❌
Xatolik!
Update da xatolik...";
                            }
    					}
    					else
    					{
    					    $reply="❌❌❌
Testni yakunlashga faqat testni yaratgan foydalanuvchining haqqi bor!!!
😉😉😉";    
    					}
    					
					}
					else
					{
   						$reply="Xatolik!\r\nTest bazadan topilmadi.\r\nTest kodini noto`g`ri yuborgan bo`lishingiz mumkin, iltimos tekshirib qaytadan yuboring.";
   						
					}

                    $conn->close();
					z:$postfields = array(
						'chat_id' => "$chat_id",
						'text' => "$reply",
						'parse_mode'=>"html");

					print_r($postfields);
					
					sendMessage($url,$postfields);
				}
		        else
				    if(substr($text,0,11)=="/natija_kod")
				    {
					    $url = $website.$bot_token."/sendMessage";
					    $test_id=substr($text,11);
					    $fan_nomi="";
					    $testsoni=0;

                        require('config.php');
                        
                        $sql = "SELECT * FROM testlar where id=".$test_id;
					    $result = $conn->query($sql);
					    if ($result->num_rows > 0) 
					    {
    					   while($row = $result->fetch_assoc()) 
    					   {
        					   $fan_nomi=$row["fan_nomi"];
        					   $testsoni=$row["testlar_soni"];
    					   }
    					        
					    }
					    
					    $reply="Test holati.

Fan: ".$fan_nomi."
Test kodi: kod".$test_id."
Savollar soni: ".$testsoni." ta

Natijalar:

";


   

					    
					    $sql = "SELECT * FROM users where test_id=".$test_id." order by count_corrects desc, sana_vaqt";
					    $result = $conn->query($sql);
					    $x=1;
					    if ($result->num_rows > 0) 
					    {
    					   while($row = $result->fetch_assoc()) 
    					   {
    					      
        					   $reply=$reply.$x." <a href='tg://user?id=".$row["chatId"]."'>".$row["fio"]."</a> - ".$row["count_corrects"]." ta\r\n";
        					   $x=$x+1;
    					   }
    					        
					    }
					    
					    $conn->close();
					    $postfields = array(
						    'chat_id' => "$chat_id",
						    'text' => "$reply",
						    'parse_mode'=>"html");

					    print_r($postfields);
					    
					    sendMessage($url,$postfields);
				    }