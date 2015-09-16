<?php

$prices1 = 'amt=15.25&ccy=UAH&details=книга Будь здоров!&ext_details=1000BDN01&
pay_way=privat24&
order=000AB1502ZGH&
merchant= 75482&
state=ок&
date=060814080113&
ref=aBESQ2509023364513&
payCountry=UA';

$pieces = explode("&", $prices1);
print_r($pieces[2]);
$details = str_replace('details=', '', $pieces[2]);
echo $details;