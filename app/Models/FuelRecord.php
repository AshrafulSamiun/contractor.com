<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class FuelRecord extends Model { protected $fillable=['record_no','vehicle_id','driver_id','fuel_date','fuel_type','uom','quantity','unit_price','total_cost','odometer_km','station_name','payment_method','receipt_path','notes','is_deleted']; protected $casts=['fuel_date'=>'date','quantity'=>'decimal:2','unit_price'=>'decimal:2','total_cost'=>'decimal:2','is_deleted'=>'boolean']; public function vehicle(){return $this->belongsTo(Vehicle::class);} public function driver(){return $this->belongsTo(Driver::class);} }
