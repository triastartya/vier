<?php

namespace Viershaka\Vier;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Viershaka\Vier\Exceptions\InvalidRepository;
use Viershaka\Vier\Exceptions\RepositoryIsRequired;
use Viershaka\Vier\Traits\ControllerAction;

class VierController extends Controller
{
    use ControllerAction;

    public $repository;
    // public $service;

    public function __construct($repository)
    {
    
        $this->validateConstructorParams($repository);

        $this->repository = $repository;
        // $this->service = $service;

        $fields = request()->fields;

        if ($fields && is_array($fields)) {
            $this->repository->setFields($fields);
        }

        if (request('keyword', '')) {
            $this->repository->setKeyword(request('keyword'));

            if (request('keywordFields', [])) {
                $this->repository->setKeywordFields(request('keywordFields'));
            }
        }

        $this->repository->setFilter($this->getFilters());
    }
    
    public function all()
    {
        $data = $this->repository->getModel()->limit(200)->get();
        return response()->json(['success'=>true,'data'=>$data]);
    }

    public function index()
    {
        
        $this->beforeIndex();
        
        $data = $this->repository->get(request('perPage', 15));
        // $data = $this->repository->setFilter($this->getFilters())->get(request('perPage', 15));

        $this->afterIndex($data);

        return responisme()
            ->withData($data)
        ->build();
    }

    public function show($id)
    {
        $this->beforeShow();

        $data = $this->repository->find($id);

        $data = $this->afterShow($data);

        return responisme()
            ->withData($data)
            ->build();
    }

    public function first()
    {
        return responisme()
            ->withData($this->repository->first())
            ->build();
    }

    public function store()
    {
        $data = DB::transaction(function () {
            $request = $this->beforeStore(request());

            $data = $this->storeData($request);

            $this->afterStore($data);
        });

        return responisme()
            ->withData($data)
            ->withHttpCode(201)
            ->build();
    }

    public function storeData(array $data)
    {
        return $this->repository->store($data);
    }

    public function update($id)
    {
        $data = $this->repository->find($id);

        DB::transaction(function () use ($data) {
            $attributes = $this->beforeUpdate(request());

            $this->repository->update($data, $attributes);

            $this->afterUpdate($data);
        });

        return responisme()
            ->withData($data->refresh())
            ->build();
    }

    public function destroy($id)
    {
        $data = $this->repository->find($id);

        DB::transaction(function () use ($data) {
            $this->beforeDestroy($data);

            $this->repository->delete($data);

            $this->afterDestroy($data);
        });

        return responisme()
            ->build();
    }

    public function datatables()
    {
        return $this->repository->datatables()->toJson();
    }

    public function table(Request $request)
    {
        return $this->repository->table($request);
    }

    public function dictionary()
    {
        return responisme()
            ->withData($this->repository->getModel()->getModelDictionary())
            ->build();
    }

    public function lov()
    {
        $model = $this->repository->getModel();
        $fields = $model->getLovFields();
        $requestFields = request()->fields;

        if ($requestFields && is_array($requestFields)) {
            $fields = array_unique(array_merge($fields, $requestFields));
        }

        $this->repository->setFields($fields)->setFilter($this->getFilters());

        return responisme()
            ->withData($this->repository->get())
            ->build();
    }

    protected function validateConstructorParams($repository)
    {
        
        $validRepositoryClasses = [
            VierRepository::class
        ];
       
        if (!isset($repository)) {
            throw new RepositoryIsRequired();
        } else {
            foreach ($validRepositoryClasses as $validRepositoryClass) {
                if (!$repository instanceof $validRepositoryClass) {
                    throw new InvalidRepository();
                }
            }
        }

        // $validServiceClasses = [
        //     VierService::class
        // ];

        // if (!isset($service)) {
        //     throw new ServiceIsRequired();
        // } else {
        //     foreach ($validServiceClasses as $validServiceClass) {
        //         if (!$service instanceof $validServiceClass) {
        //             throw new InvalidService();
        //         }
        //     }
        // }
    }

    public function guardNameIs($name)
    {
        return auth($name)->check();
    }

    public function ok($message = '')
    {
        return $message
            ? responisme()->withMessage($message)->build()
            : responisme()->build();
    }

    public function getFilters()
    {
        $requestFilters = request('filters', []);

        $filters = [];

        foreach ($requestFilters as $filter) {
            $data = $filter;

            if (is_string($data)) {
                $data = (array) json_decode($data);
            }

            array_push($filters, $data);
        }

        return $filters;
    }
}