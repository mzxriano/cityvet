
class VaccineProductModel {
  // CRITICAL: This MUST match the Lot ID from the database for inventory tracking.
  final int id; 
  final String name; // Product Name
  final String lotNumber; // Lot Number (from VaccineLot)
  final String productBrand; // Brand Name (from VaccineProduct)
  final int currentStock; // Remaining stock in doses
  final String expirationDate; // YYYY-MM-DD
  final int withdrawalDays; // Withdrawal days (from VaccineProduct)

  VaccineProductModel({
    required this.id,
    required this.name,
    required this.lotNumber,
    required this.productBrand,
    required this.currentStock,
    required this.expirationDate,
    required this.withdrawalDays,
  });

  // Factory method to create a VaccineProductModel from a JSON map (used by API service)
  factory VaccineProductModel.fromJson(Map<String, dynamic> json) {
    return VaccineProductModel(
      // Ensure all fields are correctly cast from the dynamic JSON type
      id: json['id'] as int,
      name: json['name'] as String,
      lotNumber: json['lot_number'] as String,
      productBrand: json['product_brand'] as String,
      currentStock: json['current_stock'] as int,
      expirationDate: json['expiration_date'] as String,
      withdrawalDays: json['withdrawal_days'] as int,
    );
  }

  // Method to convert the model back to JSON (useful for debugging or other endpoints)
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'lot_number': lotNumber,
      'product_brand': productBrand,
      'current_stock': currentStock,
      'expiration_date': expirationDate,
      'withdrawal_days': withdrawalDays,
    };
  }

  // Optional: Override toString for easy debugging
  @override
  String toString() {
    return '$name (Lot: $lotNumber, Stock: $currentStock)';
  }
}