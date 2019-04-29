<?
	/** @noinspection PhpFullyQualifiedNameUsageInspection */
	/** @var \Pages\SearchSightPage $this */
	/** @var \Model\City $city */
	$city = $this->city;
?>
<div id="search-ribbon--city" class="search-city-ribbon" data-lat="<?=$city->getLat();?>" data-lng="<?=$city->getLng();?>" data-radius="<?=$city->getRadius();?>"></div>