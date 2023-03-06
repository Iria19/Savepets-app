<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\I18n\FrozenTime;
/**
 * Animal Controller
 *
 * @property \App\Model\Table\AnimalTable $Animal
 * @method \App\Model\Entity\Animal[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AnimalController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['index', 'view']);
        
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index($recomendacion=null)
    {
        $keyEspecie = $this->request->getQuery('keyEspecie');
        $keyRaza = $this->request->getQuery('keyRaza');
        $keySexo = $this->request->getQuery('keySexo');
        if($recomendacion){
            $salida = shell_exec('python3 recomendar.py '.$recomendacion);
            if($salida!=NULL){
                $resultado = explode ( ',', $salida);
                $animales = $this->Animal->find('all', ['limit' => 200])->where(['or'=>[['age'=>intval($resultado[0])],['age'=>intval($resultado[1])],['age'=>intval($resultado[2])],['specie like'=>$resultado[3]],['specie like'=>$resultado[4]],['specie like'=>$resultado[5]],['race'=>$resultado[6]],['race'=>$resultado[7]],['race'=>$resultado[8]]]]);
    
            }else{
                $animales = $this->Animal;
            }
        }else{

            if($keyEspecie||$keyRaza||$keySexo){
                $animales = $this->Animal->find('all', ['limit' => 200])->where(['race like'=>'%'.$keyRaza.'%','sex like'=>'%'.$keySexo.'%','specie like'=>'%'.$keyEspecie.'%']);
            }else{
                $animales = $this->Animal;
            }
        }
        $animal = $this->paginate($animales,['contain'=>['AnimalShelter'],'order' => ['id'=>'desc']]);

        $this->set(compact('animal'));
    }

    /**
     * View method
     *
     * @param string|null $id Animal id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $animal = $this->Animal->get($id, [
            'contain' => ['AnimalShelter'],
        ]);
        $allUsers = $this->getTableLocator()->get('User');//Conecto con users
        $id_user=$animal['animal_shelter']['user_id'];
        $currentUser=$allUsers->find()->where(['id'=>$id_user])->select('name')->first();
        $currentUserIDs=$allUsers->find()->where(['id'=>$id_user])->select('id')->first();

        $currentUserName=$currentUser['name'];
        $currentUserID=$currentUserIDs['id'];
        $this->set(compact('animal','currentUserName','currentUserID'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $animal = $this->Animal->newEmptyEntity();

        if ($this->request->is('post')) {
            $animal = $this->Animal->patchEntity($animal, $this->request->getData());
             if(!$animal->getErrors){

                $image = $this->request->getData('image_file');

                if($image && ($image->getClientMediaType()!='image/png' && $image->getClientMediaType()!='image/jpg'&& $image->getClientMediaType()!='image/jpeg' && $image->getClientFilename()!="")){
                    $this->Flash->error(__('La imagen debe ser jpg, jpeg o png.'));

                }else{
                    $name=null;
                    if($image !=NULL){
                        $name  = $image->getClientFilename();
                    }

                    if( !is_dir(WWW_ROOT.'img'.DS.'animal-img') ){
                        mkdir(WWW_ROOT.'img'.DS.'animal-img',0775);
                    }

                    if($name){
                        $allUsers = $this->getTableLocator()->get('User');//Conecto con users
                        $query = $allUsers->find()->where(['id' => $animal->animal_shelter->user_id])->select('username')->first();
                        $name=$query['username'].'-'.date('d-m-y h:i:s').'-'.$name;

                        $targetPath = WWW_ROOT.'img'.DS.'animal-img'.DS.$name;
                        if($image->getStream()->getMetadata('uri')!='/var/www/html/savepets/webroot/img/testimagen.jpg'){
                            $image->moveTo($targetPath);
                        }
                        $animal->image = 'animal-img/'.$name;

                    }
                    if ($this->Animal->save($animal)) {
                        $this->Flash->success(__('El animal se ha añadido.'));
                        return $this->redirect(['action' => 'index']);
                    }else{
                        $this->Flash->error(__('El animal no se ha podido añadir, por favor intentalo de nuevo'));
                    }
                }

            }
            $this->Flash->error(__('El animal no se ha podido añadir, por favor intentalo de nuevo'));                       

        }
        $allUsers = $this->getTableLocator()->get('User');

        $user = $allUsers->find('list', ['limit' => 200])->all();
        $this->set(compact('animal','user'));
    }


    /**
     * Add through file method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function addfile()
    {
        $animal = $this->Animal->newEmptyEntity();

        if ($this->request->is('post')) {
            $file = $this->request->getData('fichero');
            $usuario=$this->request->getData();
            $usuarioid=$usuario['animal_shelter']['user_id'];
            $filetype=$file->getClientMediaType();
            
            $name=null;

            if($filetype=='application/json'|| $filetype=='text/csv')  {
                //Muevo el fichero 
                if($file !=NULL){
                   $name  = $file->getClientFilename();
                }
                if($name){

                    $targetPath = WWW_ROOT.$name;
                    if($file->getStream()->getMetadata('uri')!='/var/www/html/savepets/webroot/csvexample.csv'){
                        $file->moveTo($targetPath);
                    }
                    if($filetype=='application/json'){

                        $filecontentfull=file_get_contents($targetPath);
                        $filecontentfull=json_decode($filecontentfull);
                        $filecontentfull=(array)$filecontentfull;
                    }else{

                        $rows   = array_map('str_getcsv', file($targetPath));
                        $header = array_shift($rows);
                        $csv[]    = array();
                        foreach($rows as $row) {
                            $csv[] = array_combine($header, $row);
                        }
                        unset($csv[0]);
                        $animalshelter=[];
                        $sum=0;
                        foreach($csv as $csvarray){
                            foreach ($csvarray as $key => $value) { // $key tendrá el valor de "lug" y value el array
                                if($key=='start_date'){
                                    $animalshelter['start_date']=$value;
                                }elseif($key=='end_date'){
                                    $animalshelter['end_date']=$value;

                                }else{
                                    $filefull[$key] = $value;
                                }
                            $animalshelter['user_id']=$usuarioid;
                            $filefull['animal_shelter']=$animalshelter;
                            }        
                            
                            $filecontentfull[$sum]=$filefull;
                            $filefull=[];
                            $sum+=1;

                        }       
                                               
                    }

                    $tam=count($filecontentfull);
                    $cont=0;
                    foreach ($filecontentfull as $filecontent){

                        $filecontent=(array)$filecontent;
                        $cont+=1;

                        $animal = $this->Animal->patchEntity($animal, $filecontent);
                        $fileimagen= $filecontent["image_file"];

                        if(!$animal->getErrors){

                            $name=null;
                            $extension=null;
                            if($fileimagen!=""){
                                $urlimage = explode("/", $fileimagen);
                                $pos=sizeof($urlimage)-1;
                                $name=$urlimage[$pos];
                                $extensionsep = explode(".", $name);
                                $extension=$extensionsep[1];
                            }
                            if($extension!='png' && $extension!='jpg'&& $extension!='jpeg' && $extension!=null){
                                $this->Flash->error(__('La imagen debe ser jpg, jpeg o png.'));
                            }else{

                                if($name){
                                    if( !is_dir(WWW_ROOT.'img'.DS.'animal-img') ){
                                        mkdir(WWW_ROOT.'img'.DS.'animal-img',0775);
                                    }

                                    $allUsers = $this->getTableLocator()->get('User');//Conecto con users
                                    $query = $allUsers->find()->where(['id' => $animal->animal_shelter->user_id])->select('username')->first();
                                    $name=$query['username'].'-'.date('d-m-y h:i:s').'-'.$name;
                                    $targetPath = WWW_ROOT.'img'.DS.'animal-img'.DS.$name;
                                    $animal->image = 'animal-img/'.$name;
                                    copy($fileimagen,$targetPath);
                                }
                                if ($this->Animal->save($animal)) {
                                    $this->Flash->success(__('El animal se ha añadido.'));
                                    if($tam==$cont){
                                        return $this->redirect(['action' => 'index']);
                                    }
                                }else{

                                    $this->Flash->error(__('El animal no se ha podido añadir, por favor intentalo de nuevo'));
                                }
                            }
                        }
                    }
                $this->Flash->error(__('El animal no se ha podido añadir, por favor revisa los campos e intentalo de nuevo.'));
                }else{
                    $this->Flash->error(__('Debes añadir un fichero.'));
                }
            }else{
                $this->Flash->error(__('El fichero para añadir varios animales debe ser un JSON o CSV.'));
            }    
        }
        $allUsers = $this->getTableLocator()->get('User');

        $user = $allUsers->find('list', ['limit' => 200])->all();
        $this->set(compact('animal','user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Animal id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {           
        $animal = $this->Animal->get($id, [
            'contain' => ['AnimalShelter'],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $animal = $this->Animal->patchEntity($animal, $this->request->getData());
            if (!$animal->getErrors) {

                $image = $this->request->getData('change_image');
                if($image && ($image->getClientMediaType()!='image/png' && $image->getClientMediaType()!='image/jpg'&& $image->getClientMediaType()!='image/jpeg' && $image->getClientFilename()!="")){
                    $this->Flash->error(__('La imagen debe ser jpg, jpeg o png.'));
                }else{
                    $name=null;

                    if($image !=NULL){
                        $name  = $image->getClientFilename();

                    }
                    if (!is_dir(WWW_ROOT . 'img' . DS . 'animal-img')){
                        mkdir(WWW_ROOT . 'img' . DS . 'animal-img', 0775);
                    }
                    if($name){

                        $allUsers = $this->getTableLocator()->get('User');//Conecto con users
                        $query = $allUsers->find()->where(['id' => $animal->animal_shelter->user_id])->select('username')->first();
                        $name=$query['username'].'-'.date('d-m-y h:i:s').'-'.$name;
                        $targetPath = WWW_ROOT . 'img' . DS . 'animal-img' . DS . $name;

                        $imgpath = WWW_ROOT . 'img' . DS . $animal->image;
                        if (file_exists($imgpath)&&!preg_match('/^\/var\/www\/html\/savepets\/webroot\/img\/animal-img\/$/',$imgpath)&& !preg_match('/^\/var\/www\/html\/savepets\/webroot\/img\/$/',$imgpath)) {
                            unlink($imgpath);
                        }
                        if($image->getStream()->getMetadata('uri')!='/var/www/html/savepets/webroot/img/testimagen.jpg'){
              
                            $image->moveTo($targetPath);
                        }
                    $animal->image = 'animal-img/' . $name;
                    }
            
                
                    if ($this->Animal->save($animal)) {
                        $this->Flash->success(__('El animal se ha editado correctamente.'));
                        return $this->redirect(['action' => 'index']);
                    }else{
                        $this->Flash->error(__('El animal no se ha podido editar, por favor intentalo de nuevo.'));
                    }
                }
            }
                $this->Flash->error(__('El animal no se ha podido editar, por favor intentalo de nuevo.'));
            }

        $allUser = $this->getTableLocator()->get('User');
        $user_id_animalShelter=$animal->animal_shelter->user_id;
        $usercomplete=$allUser->find()->where(['id'=>$user_id_animalShelter])->select('name')->first();
        $userName=$usercomplete['name'];


        $allUsers = $this->getTableLocator()->get('User');

        $user = $allUsers->find('list', ['limit' => 200])->all();
        $this->set(compact('animal','userName','user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Animal id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $animal = $this->Animal->get($id);
        $imgpath = WWW_ROOT.'img'.DS.$animal->image;

        if ($this->Animal->delete($animal)) {
            if(file_exists($imgpath) ){
                $imageAnimal=$animal->image;
                if(!empty($imageAnimal)&& !preg_match('/^animal-img\/$/',$imageAnimal)){
                    unlink($imgpath);
                }
            }
            $this->Flash->success(__('El animal se ha eliminado.'));
        } else {
            $this->Flash->error(__('El animal no se ha podido eliminar, por favor intentalo de nuevo.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
