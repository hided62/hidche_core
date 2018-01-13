<?php

class _Queue {
    private $capacity;
    private $size;
    private $head;
    private $tail;
    private $arr;

    public function Queue($capacity) {
        $this->capacity = $capacity;
        $this->size = 0;
        $this->head = 0;
        $this->tail = 0;
    }

    public function getSize() {
        return $this->size;
    }

    public function clear() {
        $this->size = 0;
        $this->head = 0;
        $this->tail = 0;
    }

    public function push($value) {
        if($this->size >= $this->capacity) return;
        $this->arr[$this->tail] = $value;
        $this->tail = ($this->tail + 1) % $this->capacity;
        $this->size++;
    }

    public function pop() {
        if($this->size <= 0) return null;
        $value = $this->arr[$this->head];
        $this->head = ($this->head + 1) % $this->capacity;
        $this->size--;
        return $value;
    }
}

