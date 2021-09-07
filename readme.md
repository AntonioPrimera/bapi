#BAPI Version history

##v.1.2 - 02.03.2021

###Enhancements

- Added trait HandlesModelRelations with methods for checking if a model has any dependent
  related models - this is very useful when trying to delete a model which has other
  dependent models, which might hinder the deletion of the original model
- Added this version history, to document BAPI versions, backwards compatibility and any
  necessary update actions from one version to another
- Started adding comments with "Verified: BAPI v.x.x" and "UnitTests: x%" to each BAPI which
  was verified - a report should be created, to check all BAPIs for their verified
  status, and their reported test coverage


##v.1.1 - 20.02.2021

###Enhancements

- Added trait HandlesExceptions
- Added protected attribute $throw, with a default list of Exceptions to be
  thrown automatically (not handled by method handleException)
  
###Actions

- Required simplification of all existing BAPIs, removing manual handling of
  the default Exceptions, which are thrown automatically


##v.1.0 - 01.02.2021

- Basic BAPI Functionality