<?php

namespace App\Http\Livewire;

use App\Actions\Album\Photos;
use App\Actions\Albums\Extensions\PublicIds;
use App\Factories\AlbumFactory;
use Livewire\Component;

class Album extends Component
{
	/**
	 * @var int
	 */
	public $albumId;

	/**
	 * @var Album
	 */
	public $album;

	/**
	 * @var array (for now)
	 */
	public $info;

	/**
	 * @var array (for now)
	 */
	public $photos;

	/**
	 * @var AlbumFactory
	 */
	private $albumFactory;

	private $photosAction;

	public function mount($album, AlbumFactory $albumFactory, Photos $photosAction)
	{
		$this->album = $album;
		$this->info = [];
		$this->info['albums'] = [];

		$this->albumFactory = $albumFactory;
		$this->photosAction = $photosAction;
	}

	public function render()
	{
		if ($this->album->smart) {
			$publicAlbums = resolve(PublicIds::class)->getPublicAlbumsId();
			$this->album->setAlbumIDs($publicAlbums);
		} else {
			// we only do this when not in smart mode (i.e. no sub albums)
			// that way we limit the number of times we have to query.
			resolve(PublicIds::class)->setAlbum($this->album);
		}
		$this->info = $this->album->toReturnArray();

		// take care of sub albums
		$this->info['albums'] = $this->album->get_children()->map(fn ($a) => $a->toReturnArray())->values();

		// take care of photos
		$this->photos = $this->photosAction->get($this->album);
		$this->info['id'] = strval($this->album->id);
		$this->info['num'] = strval(count($this->photos));

		return view('livewire.album');
	}
}