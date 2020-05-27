<?php

//this uses https://en.wikipedia.org/wiki/Semaphore_(programming)
//it is necessary to install php7-sysvsem
class PoorManMutex
{
    public $key;
    private $sem;
    private bool $autoUnlock = false;

    public function __destruct()
    {
        if ($this->locked) {
            if ($this->autoUnlock) {
                $this->unlock();
            } else {
                die("deadlock prevented!" . getTrace());
            }
        }
    }


    /**
     * php do not support checking sem status in a non blocking way
     * so this will prevent deadlock
     */
    private bool $locked = false;

    public function __construct(int $key)
    {
        $this->key = $key;
        $this->sem = sem_get($this->key, 1);
        if ($this->sem < 1) {
            die("this should not happen, sem id negative: $sem \n" . getTrace());
        }
    }


    public function lock()
    {
        if (!$this->locked) {
            sem_acquire($this->sem, false);
            $this->locked = true;
        } else {
            die("deadlock prevented!" . getTrace());
        }
    }

    public function tryLock()
    {
        if (!$this->locked) {
            $this->locked = sem_acquire($this->sem, true);
            return $this->locked;
        } else {
            die("deadlock prevented!" . getTrace());
        }
    }

    public function unlock()
    {
        $this->locked = false;
        sem_release($this->sem);
    }

    /**
     * @param bool $autoUnlock
     */
    public function setAutoUnlock(bool $autoUnlock = true): void
    {
        $this->autoUnlock = $autoUnlock;
    }
}
