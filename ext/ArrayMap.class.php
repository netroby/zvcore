<?php
//ArrayMap��⣬���԰�array����ת���ɶ�Ӧ��object

class ArrayMap extends ArrayObject{


	// ��ȡ arrayobject ����

	public function __construct(array $array = array()){

		foreach ($array as &$value){

			if(is_array($value) && isset($value)){
				$value = new self($value);
			}

		}

		parent::__construct($array);

	}



	// ȡֵ

	public function __get($index){

		return $this->offsetGet($index);

	}



	// ��ֵ

	public function __set($index, $value){

		if(is_array($value) && isset($value)){
			$value = new self($value);
		}

		$this->offsetSet($index, $value);

	}



	// �Ƿ����

	public function __isset($index){

		return $this->offsetExists($index);

	}



	// ɾ��

	public function __unset($index){

		$this->offsetUnset($index);

	}



	// ת��Ϊ��������

	public function toArray(){

		$array = $this->getArrayCopy();

		foreach ($array as &$value){

			if($value instanceof self) {
				$value = $value->toArray();
			}

		}

		return $array;

	}



	// ��ӡ���ַ�

	public function __toString(){

		return var_export($this->toArray(), true);

	}

	

	// ����������ֵ

	public function put($index,$value){

		if(is_array($value) && isset($value) ){
			$value = new self($value);
		}

		$this->offsetSet($index, $value);

	}

	

	// ��������ȡֵ

	public function get($index){

		return $this->offsetGet($index);

	}


}
