<?php
namespace Modules\NsDemo\Crud;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\CrudService;
use App\Services\Users;
use App\Services\CrudEntry;
use App\Exceptions\NotAllowedException;
use App\Models\User;
use TorMorten\Eventy\Facades\Events as Hook;
use Exception;
use Modules\NsDemo\Models\DemoInstance;
use Modules\NsDemo\Services\ForgeService;

class DemoInstancesCrud extends CrudService
{
    /**
     * define the base table
     * @param string
     */
    protected $table      =   'ns_demo_instances';

    /**
     * default slug
     * @param string
     */
    protected $slug   =   'demo-instances';

    /**
     * Define namespace
     * @param string
     */
    protected $namespace  =   'ns-demo-instances';

    /**
     * Model Used
     * @param string
     */
    protected $model      =   DemoInstance::class;

    /**
     * Define permissions
     * @param array
     */
    protected $permissions  =   [
        'create'    =>  true,
        'read'      =>  true,
        'update'    =>  true,
        'delete'    =>  true,
    ];

    /**
     * Adding relation
     * Example : [ 'nexopos_users as user', 'user.id', '=', 'nexopos_orders.author' ]
     * @param array
     */
    public $relations   =  [
            ];

    /**
     * all tabs mentionned on the tabs relations
     * are ignored on the parent model.
     */
    protected $tabsRelations    =   [
        // 'tab_name'      =>      [ YourRelatedModel::class, 'localkey_on_relatedmodel', 'foreignkey_on_crud_model' ],
    ];

    /**
     * Export Columns defines the columns that
     * should be included on the exported csv file.
     */
    protected $exportColumns    =   []; // @getColumns will be used by default.

    /**
     * Pick
     * Restrict columns you retrieve from relation.
     * Should be an array of associative keys, where
     * keys are either the related table or alias name.
     * Example : [
     *      'user'  =>  [ 'username' ], // here the relation on the table nexopos_users is using "user" as an alias
     * ]
     */
    public $pick        =   [];

    /**
     * Define where statement
     * @var array
    **/
    protected $listWhere    =   [];

    /**
     * Define where in statement
     * @var array
     */
    protected $whereIn      =   [];

    /**
     * Fields which will be filled during post/put
     */
    
    /**
     * If few fields should only be filled
     * those should be listed here.
     */
    public $fillable    =   [];

    /**
     * If fields should be ignored during saving
     * those fields should be listed here
     */
    public $skippable   =   [];

    /**
     * Determine if the options column should display
     * before the crud columns
     */
    protected $prependOptions     =   false;

    /**
     * Define Constructor
     * @param
     */
    public function __construct()
    {
        parent::__construct();

        Hook::addFilter( $this->namespace . '-crud-actions', [ $this, 'addActions' ], 10, 2 );
    }

    /**
     * Return the label used for the crud
     * instance
     * @return array
    **/
    public function getLabels()
    {
        return [
            'list_title'            =>  __( 'DemoInstances List' ),
            'list_description'      =>  __( 'Display all demoinstances.' ),
            'no_entry'              =>  __( 'No demoinstances has been registered' ),
            'create_new'            =>  __( 'Add a new demoinstance' ),
            'create_title'          =>  __( 'Create a new demoinstance' ),
            'create_description'    =>  __( 'Register a new demoinstance and save it.' ),
            'edit_title'            =>  __( 'Edit demoinstance' ),
            'edit_description'      =>  __( 'Modify  Demoinstance.' ),
            'back_to_list'          =>  __( 'Return to DemoInstances' ),
        ];
    }

    /**
     * Check whether a feature is enabled
     * @return boolean
    **/
    public function isEnabled( $feature ): bool
    {
        return false; // by default
    }

    /**
     * Fields
     * @param object/null
     * @return array of field
     */
    public function getForm( $entry = null )
    {
        /**
         * @var ForgeService
         */
        $forgeService   =   app()->make( ForgeService::class );

        return [
            'main' =>  [
                'label'         =>  __( 'Name' ),
                'name'          =>  'name',
                'value'         =>  $entry->name ?? '',
                'description'   =>  __( 'Provide a name to the resource.' )
            ],
            'tabs'  =>  [
                'general'   =>  [
                    'label'     =>  __( 'General' ),
                    'fields'    =>  [
                        [
                            'name'          =>  'forge_id',
                            'label'         =>  __( 'Select Site Demo' ),
                            'type'          =>  'multiselect',
                            'options'       =>  collect( $forgeService->getSites() )->map( function( $sites, $serverId ) {
                                return collect( $sites )->map( fn( $site ) => [
                                    'label' =>  $site[ 'name' ],
                                    'value' =>  $serverId . '-' . $site[ 'id' ]
                                ]);
                            })->flatten(1),
                            'value'         =>  ( $entry ? json_decode( $entry->forge_id ) : null ) ?? null,
                            'description'   =>  __( 'Those instances will be managed by the module. Be careful as the selected instances will be reset periodically.' )
                        ], [
                            'type'  =>  'textarea',
                            'name'  =>  'commands',
                            'label' =>  __( 'Commands' ),
                            'description'   =>  __( 'The following command will execute on the selected instance.'),
                            'value' =>  $entry->commands ?? '',
                        ], [
                            'type'  =>  'textarea',
                            'name'  =>  'description',
                            'label' =>  __( 'Description' ),
                            'description'   =>  __( 'Provide futher details regarding the selected instance.'),
                            'value' =>  $entry->description ?? '',
                        ], 
                    ]
                ]
            ]
        ];
    }

    /**
     * Filter POST input fields
     * @param array of fields
     * @return array of fields
     */
    public function filterPostInputs( $inputs )
    {
        if ( ! empty( $inputs[ 'forge_id' ] ) ) {
            $inputs[ 'forge_id' ]   =   json_encode( $inputs[ 'forge_id' ] );
        }

        return $inputs;
    }

    /**
     * Filter PUT input fields
     * @param array of fields
     * @return array of fields
     */
    public function filterPutInputs( $inputs, DemoInstance $entry )
    {
        if ( ! empty( $inputs[ 'forge_id' ] ) ) {
            $inputs[ 'forge_id' ]   =   json_encode( $inputs[ 'forge_id' ] );
        }

        return $inputs;
    }

    /**
     * Before saving a record
     * @param Request $request
     * @return void
     */
    public function beforePost( $request )
    {
        if ( $this->permissions[ 'create' ] !== false ) {
            ns()->restrict( $this->permissions[ 'create' ] );
        } else {
            throw new NotAllowedException;
        }

        return $request;
    }

    /**
     * After saving a record
     * @param Request $request
     * @param DemoInstance $entry
     * @return void
     */
    public function afterPost( $request, DemoInstance $entry )
    {
        return $request;
    }


    /**
     * get
     * @param string
     * @return mixed
     */
    public function get( $param )
    {
        switch( $param ) {
            case 'model' : return $this->model ; break;
        }
    }

    /**
     * Before updating a record
     * @param Request $request
     * @param object entry
     * @return void
     */
    public function beforePut( $request, $entry )
    {
        if ( $this->permissions[ 'update' ] !== false ) {
            ns()->restrict( $this->permissions[ 'update' ] );
        } else {
            throw new NotAllowedException;
        }

        return $request;
    }

    /**
     * After updating a record
     * @param Request $request
     * @param object entry
     * @return void
     */
    public function afterPut( $request, $entry )
    {
        return $request;
    }

    /**
     * Before Delete
     * @return void
     */
    public function beforeDelete( $namespace, $id, $model ) {
        if ( $namespace == 'ns-demo-instances' ) {
            /**
             *  Perform an action before deleting an entry
             *  In case something wrong, this response can be returned
             *
             *  return response([
             *      'status'    =>  'danger',
             *      'message'   =>  __( 'You\re not allowed to do that.' )
             *  ], 403 );
            **/
            if ( $this->permissions[ 'delete' ] !== false ) {
                ns()->restrict( $this->permissions[ 'delete' ] );
            } else {
                throw new NotAllowedException;
            }
        }
    }

    /**
     * Define Columns
     * @return array of columns configuration
     */
    public function getColumns() {
        return [
            
            'name'  =>  [
                'label'  =>  __( 'Name' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'forge_id'  =>  [
                'label'  =>  __( 'Forge_id' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'author'  =>  [
                'label'  =>  __( 'Author' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'updated_at'  =>  [
                'label'  =>  __( 'Updated_at' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
        ];
    }

    /**
     * Define actions
     */
    public function addActions( CrudEntry $entry, $namespace )
    {
        /**
         * Declaring entry actions
         */
        $entry->addAction( 'edit', [
            'label'         =>      __( 'Edit' ),
            'namespace'     =>      'edit',
            'type'          =>      'GOTO',
            'url'           =>      ns()->url( '/dashboard/' . $this->slug . '/edit/' . $entry->id )
        ]);

        $entry->addAction( 'trigger', [
            'label'         =>      __( 'Trigger' ),
            'namespace'     =>      'trigger',
            'type'          =>      'GET',
            'confirm'   =>  [
                'message'  =>  __( 'Would you like to trigger deployment on the attached instances?' ),
            ],
            'url'           =>      ns()->url( '/dashboard/' . $this->slug . '/trigger/' . $entry->id )
        ]);

        $entry->addAction( 'delete', [
            'label'     =>  __( 'Delete' ),
            'namespace' =>  'delete',
            'type'      =>  'DELETE',
            'url'       =>  ns()->url( '/api/nexopos/v4/crud/ns-demo-instances/' . $entry->id ),
            'confirm'   =>  [
                'message'  =>  __( 'Would you like to delete this ?' ),
            ]
        ]);

        return $entry;
    }


    /**
     * Bulk Delete Action
     * @param  object Request with object
     * @return  false/array
     */
    public function bulkAction( Request $request )
    {
        /**
         * Deleting licence is only allowed for admin
         * and supervisor.
         */

        if ( $request->input( 'action' ) == 'delete_selected' ) {

            /**
             * Will control if the user has the permissoin to do that.
             */
            if ( $this->permissions[ 'delete' ] !== false ) {
                ns()->restrict( $this->permissions[ 'delete' ] );
            } else {
                throw new NotAllowedException;
            }

            $status     =   [
                'success'   =>  0,
                'failed'    =>  0
            ];

            foreach ( $request->input( 'entries' ) as $id ) {
                $entity     =   $this->model::find( $id );
                if ( $entity instanceof DemoInstance ) {
                    $entity->delete();
                    $status[ 'success' ]++;
                } else {
                    $status[ 'failed' ]++;
                }
            }
            return $status;
        }

        return Hook::filter( $this->namespace . '-catch-action', false, $request );
    }

    /**
     * get Links
     * @return array of links
     */
    public function getLinks(): array
    {
        return  [
            'list'      =>  ns()->url( 'dashboard/' . 'demo-instances' ),
            'create'    =>  ns()->url( 'dashboard/' . 'demo-instances/create' ),
            'edit'      =>  ns()->url( 'dashboard/' . 'demo-instances/edit/{id}' ),
            'post'      =>  ns()->url( 'api/nexopos/v4/crud/' . 'ns-demo-instances' ),
            'put'       =>  ns()->url( 'api/nexopos/v4/crud/' . 'ns-demo-instances/{id}' . '' ),
        ];
    }

    /**
     * Get Bulk actions
     * @return array of actions
    **/
    public function getBulkActions(): array
    {
        return Hook::filter( $this->namespace . '-bulk', [
            [
                'label'         =>  __( 'Delete Selected Groups' ),
                'identifier'    =>  'delete_selected',
                'url'           =>  ns()->route( 'ns.api.crud-bulk-actions', [
                    'namespace' =>  $this->namespace
                ])
            ]
        ]);
    }

    /**
     * get exports
     * @return array of export formats
    **/
    public function getExports()
    {
        return [];
    }
}
