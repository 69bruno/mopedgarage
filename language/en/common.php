<?php
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'MOPEDGARAGE'                    => 'Mopedgarage',
	'MOPEDGARAGE_GALLERY'            => 'Gallery',
	'MOPEDGARAGE_GALLERY_TITLE'      => 'Mopedgarage',
	'MOPEDGARAGE_GALLERY_EXPLAIN'    => 'Browse the community motorcycles in a clean gallery view.',
	'MOPEDGARAGE_GALLERY_ALL_HEADING'=> 'All motorcycles',
	'MOPEDGARAGE_GALLERY_ALL_COUNT'  => 'Displayed motorcycles',
	'MOPEDGARAGE_GALLERY_OWNER'      => 'Owner',

	'MOPEDGARAGE_NO_ENTRIES'         => 'No motorcycles available yet.',
	'MOPEDGARAGE_NO_ENTRIES_EXPLAIN' => 'As soon as members add motorcycles, they will appear here automatically.',
	'MOPEDGARAGE_EMPTY_TITLE'        => 'Nothing to show yet',

	'MOPEDGARAGE_SHOW_PROFILE'       => 'View profile',
	'MOPEDGARAGE_SHOW_OWNER'         => 'Go to owner profile',
	'MOPEDGARAGE_OWNER'              => 'Owner',
	'MOPEDGARAGE_BRAND'              => 'Brand',
	'MOPEDGARAGE_MODEL'              => 'Model',
	'MOPEDGARAGE_YEAR'               => 'Year',
	'MOPEDGARAGE_COLOR'              => 'Colour',
	'MOPEDGARAGE_DISPLACEMENT'       => 'Displacement',
	'MOPEDGARAGE_DISPLACEMENT_SHORT' => 'ccm',
	'MOPEDGARAGE_UNKNOWN'            => 'Unknown',

	'MOPEDGARAGE_MORE_IMAGES'        => 'More images',
	'MOPEDGARAGE_PREVIOUS'           => 'Previous',
	'MOPEDGARAGE_NEXT'               => 'Next',
	'MOPEDGARAGE_OPEN_IMAGE'         => 'Open image',
	'MOPEDGARAGE_IMAGE_OF'           => 'Image of %s',
	'MOPEDGARAGE_LIGHTBOX_CLOSE'     => 'Close',

	'MOPEDGARAGE_FILTER_ALL'         => 'All',

	'MOPEDGARAGE_SEARCH_TITLE'              => 'Mopedgarage search',
	'MOPEDGARAGE_SEARCH_EXPLAIN'            => 'Filter the public Mopedgarage by brand, model, year, ccm or only entries with images.',
	'MOPEDGARAGE_SEARCH_RESULTS_HEADING'    => 'Filtered motorcycles',
	'MOPEDGARAGE_SEARCH_RESULTS_COUNT'      => 'Results',
	'MOPEDGARAGE_SEARCH_NO_RESULTS'         => 'No motorcycles found.',
	'MOPEDGARAGE_SEARCH_VIEW_PROFILE'       => 'View profile',
	'MOPEDGARAGE_SEARCH_ENTER_CRITERIA'     => 'Please enter at least one search criterion.',
	'MOPEDGARAGE_SEARCH_QUERY_LABEL'        => 'Mopedgarage search',
	'MOPEDGARAGE_SEARCH_QUERY_HELP'         => 'Search by brand, model, year or ccm. Wildcards like % are not required.',
	'MOPEDGARAGE_SEARCH_WITH_IMAGE'         => 'Image filter',
	'MOPEDGARAGE_SEARCH_WITH_IMAGE_SHORT'   => 'with image only',
	'MOPEDGARAGE_SEARCH_WITH_IMAGE_EXPLAIN' => 'Show only motorcycles that have an image',
]);
