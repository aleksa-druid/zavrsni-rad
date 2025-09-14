<?php

namespace App\Livewire;

use Livewire\Component;

class Ping extends \Livewire\Component {
  public int $n = 0;
  public function inc(){ $this->n++; }
  public function render(){ return view('livewire.ping'); }
}
