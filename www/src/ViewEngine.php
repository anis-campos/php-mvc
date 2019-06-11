<?php

namespace myMVC;


interface ViewEngine
{
    public function setModel($model);

    public function setFile(string $fileName);

    public function render();
}
